<?php

namespace App\Services;

use App\Models\WorkInterval;
use Illuminate\Support\Facades\DB;

class ScheduleService
{
    public function paintInterval(
        int $employeeId,
        string $date,
        string $start,
        string $end,
        string $type, // work|busy|off
        ?int $projectId = null,
        ?string $comment = null,
    ): void {
        DB::transaction(function () use ($employeeId, $date, $start, $end, $type, $projectId, $comment) {
            // Advisory-lock на (employee_id, date)
            // Используем однопараметровый вариант с 64-битным ключом, чтобы избежать переполнения int4
            // Ключ строим стабильным образом: employeeId|date -> hashtextextended(text, 0)
            DB::select('SELECT pg_advisory_xact_lock(hashtextextended(?, 0))', [
                $employeeId . '|' . $date,
            ]);

            // Нормализуем вход
            $norm = function (string $t): string {
                if (preg_match('/^(\d{1,2}):(\d{2})/', $t, $m)) {
                    return str_pad($m[1], 2, '0', STR_PAD_LEFT) . ':' . $m[2];
                }
                return $t;
            };
            $start = $norm($start);
            $end   = $norm($end);

            // Откладываем проверку EXCLUDE до конца транзакции
            DB::statement('SET CONSTRAINTS no_overlap_per_employee DEFERRED');

            // Находим все пересечения и блокируем их
            $overlaps = WorkInterval::where('employee_id', $employeeId)
                ->where('date', $date)
                ->where(function ($q) use ($start, $end) {
                    $q->where('start_time', '<', $end)
                      ->where('end_time', '>', $start);
                })
                ->lockForUpdate()
                ->get();

            foreach ($overlaps as $old) {
                $pieces = [];
                if ($old->start_time < $start) {
                    $pieces[] = [
                        'employee_id' => $old->employee_id,
                        'project_id'  => $old->project_id,
                        'date'        => $old->date,
                        'start_time'  => $old->start_time,
                        'end_time'    => $start,
                        'type'        => $old->type,
                        'comment'     => $old->comment,
                        'hour_rate'   => $old->hour_rate,
                        'project_rate'=> $old->project_rate,
                    ];
                }
                if ($old->end_time > $end) {
                    $pieces[] = [
                        'employee_id' => $old->employee_id,
                        'project_id'  => $old->project_id,
                        'date'        => $old->date,
                        'start_time'  => $end,
                        'end_time'    => $old->end_time,
                        'type'        => $old->type,
                        'comment'     => $old->comment,
                        'hour_rate'   => $old->hour_rate,
                        'project_rate'=> $old->project_rate,
                    ];
                }

                // Удаляем старый и только потом добавляем остатки
                $old->delete();
                foreach ($pieces as $piece) {
                    WorkInterval::create($piece);
                }

                // Если мы чистим (type=work) и интервал удалён полностью,
                // но на нём была зафиксирована ставка (hour_rate/project_rate),
                // оставляем нулевой placeholder 00:00–00:00, чтобы не потерять оплату «за проект»
                if ($type === 'work' && empty($pieces) && ($old->project_rate !== null || $old->hour_rate !== null)) {
                    WorkInterval::create([
                        'employee_id' => $old->employee_id,
                        'project_id'  => $old->project_id,
                        'date'        => $old->date,
                        'start_time'  => '00:00',
                        'end_time'    => '00:00',
                        'type'        => 'busy',
                        'comment'     => $old->comment,
                        'hour_rate'   => $old->hour_rate,
                        'project_rate'=> $old->project_rate,
                    ]);
                }
            }

            // Добавляем новый интервал (если не "work" — т.е. не очистка)
            if ($type !== 'work') {
                WorkInterval::create([
                    'employee_id' => $employeeId,
                    'project_id'  => $projectId,
                    'date'        => $date,
                    'start_time'  => $start,
                    'end_time'    => $end,
                    'type'        => $type,
                    'comment'     => $comment,
                ]);
            }

            // Схлопываем соседей одинакового типа
            $this->mergeNeighbors($employeeId, $date);

            // Чистим нулевые интервалы, если вдруг где-то образовались
            WorkInterval::where('employee_id', $employeeId)
                ->where('date', $date)
                ->whereColumn('start_time', '=', 'end_time')
                ->whereNull('hour_rate')
                ->whereNull('project_rate')
                ->delete();
        });
    }

    public function mergeNeighbors(int $employeeId, string $date): void
    {
        // Ищем соседи одного типа по возрастанию времени
        $rows = WorkInterval::where('employee_id', $employeeId)
            ->where('date', $date)
            ->orderBy('start_time')
            ->get();

        $toDeleteIds = [];
        $prev = null;
        foreach ($rows as $row) {
            if ($prev && $prev->type === $row->type && $prev->end_time === $row->start_time) {
                // расширяем предыдущий
                WorkInterval::whereKey($prev->id)->update(['end_time' => $row->end_time]);
                $toDeleteIds[] = $row->id;
                // обновляем prev конец
                $prev->end_time = $row->end_time;
            } else {
                $prev = $row;
            }
        }

        if (!empty($toDeleteIds)) {
            WorkInterval::whereIn('id', $toDeleteIds)->delete();
        }
    }

    /**
     * Пересобрать день в непрерывные непересекающиеся интервалы с приоритетом последней покраски.
     */
    private function rebuildDay(array $items): array
    {
        // Нормализуем границы и сортируем по start_time, затем по приоритету (последний выигрывает)
        usort($items, function ($a, $b) {
            // Сортировка по start_time, затем по type (work < off < busy) для стабильного приоритета
            $cmp = strcmp($a['start_time'], $b['start_time']);
            if ($cmp !== 0) return $cmp;
            $order = ['work' => 0, 'off' => 1, 'busy' => 2];
            return ($order[$a['type']] ?? 1) <=> ($order[$b['type']] ?? 1);
        });

        // Алгоритм “line sweep” по минутным меткам
        $points = [];
        foreach ($items as $idx => $it) {
            $points[] = ['t' => $it['start_time'], 'i' => $idx, 'kind' => 'start'];
            $points[] = ['t' => $it['end_time'],   'i' => $idx, 'kind' => 'end'];
        }
        usort($points, function ($x, $y) {
            $cmp = strcmp($x['t'], $y['t']);
            if ($cmp !== 0) return $cmp;
            // Сначала закрываем старые (end), потом открываем новые (start), чтобы сшивка не оставляла щелей
            return strcmp($x['kind'], $y['kind']);
        });

        $active = [];
        $result = [];
        $cursor = null;

        $pushSegment = function ($from, $to) use (&$active, &$result, $items) {
            if ($from === null || $from === $to) return;
            $top = end($active); // последняя покраска имеет приоритет
            if ($top === false) return;
            $it = $items[$top];
            $last = end($result);
            if ($last && $last['type'] === $it['type'] && $last['end_time'] === $from) {
                // схлопываем соседей
                $result[key($result)]['end_time'] = $to;
            } else {
                $row = $it;
                $row['start_time'] = $from;
                $row['end_time']   = $to;
                $result[] = $row;
            }
        };

        foreach ($points as $p) {
            if ($cursor !== null && $cursor !== $p['t']) {
                $pushSegment($cursor, $p['t']);
            }
            $cursor = $p['t'];

            if ($p['kind'] === 'start') {
                $active[] = $p['i'];
            } else {
                // удаляем из активных
                $pos = array_search($p['i'], $active, true);
                if ($pos !== false) array_splice($active, $pos, 1);
            }
        }

        return $result;
    }
}


