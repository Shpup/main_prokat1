<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Estimate extends Model
{
    protected $fillable = ['project_id', 'name', 'client_id', 'company_id', 'delivery_cost'];
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    // Новая связь с компанией
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function equipment()
    {
        return $this->belongsToMany(Equipment::class, 'estimate_equipment')
            ->withPivot('status', 'quantity')
            ->withTimestamps();
    }

    public function attachEquipment($equipmentId, $quantity = 1, $status = 'assigned')
    {
        $equipment = Equipment::findOrFail($equipmentId);

        // Проверка конфликтов только если даты проекта заданы
        $conflicts = false;
        if ($this->project->start_date && $this->project->end_date) {
            $conflicts = DB::table('estimate_equipment')
                ->join('estimates', 'estimate_equipment.estimate_id', '=', 'estimates.id')
                ->join('projects', 'estimates.project_id', '=', 'projects.id')
                ->where('estimate_equipment.equipment_id', $equipmentId)
                ->where('estimate_equipment.status', 'assigned') // только assigned проверяем
                ->where('projects.id', '!=', $this->project_id)
                ->where(function ($query) {
                    $query->whereBetween('projects.start_date', [$this->project->start_date, $this->project->end_date])
                        ->orWhereBetween('projects.end_date', [$this->project->start_date, $this->project->end_date])
                        ->orWhere(function ($q) {
                            $q->where('projects.start_date', '<=', $this->project->start_date)
                                ->where('projects.end_date', '>=', $this->project->end_date);
                        });
                })
                ->exists();
        }

        if ($conflicts) {
            throw new \Exception('Оборудование занято в пересекающемся периоде другого проекта.');
        }

        // Attach или update quantity
        $existing = $this->equipment()->where('equipment_id', $equipmentId)->first();
        if ($existing) {
            $this->equipment()->updateExistingPivot($equipmentId, ['quantity' => $existing->pivot->quantity + $quantity, 'status' => $status]);
        } else {
            $this->equipment()->attach($equipmentId, ['quantity' => $quantity, 'status' => $status]);
        }
    }

    public function detachEquipment($equipmentId)
    {
        $this->equipment()->detach($equipmentId);
    }

    public function getEstimate()
    {
        $equipmentCost = 0;
        $materialsCost = 0;
        $staffCost = 0;
        $deliveryCost = (float) $this->delivery_cost ?? 0;

        // Строим отдельные деревья для оборудования и материалов
        $eqTree = [];
        $matTree = [];
        $eqDetails = [];
        $matDetails = [];
        foreach ($this->equipment as $eq) {
            if ($eq->pivot->status !== 'assigned' && $eq->pivot->status !== 'used') continue; // только прикреплённое

            $path = $this->getCategoryPath($eq->category_id);
            if ($eq->is_consumable) {
                $ref = &$matTree;
            } else {
                $ref = &$eqTree;
            }
            $lastCatRef = null; // Ссылка на последнюю категорию
            foreach ($path as $catName) {
                if (!isset($ref[$catName])) {
                    $ref[$catName] = ['sub' => [], 'equipment' => []];
                }
                $lastCatRef = &$ref[$catName];
                $ref = &$ref[$catName]['sub'];
            }

            $name = $eq->name;
            // Добавляем в 'equipment' последней категории
            if (!isset($lastCatRef['equipment'][$name])) {
                $lastCatRef['equipment'][$name] = [
                    'id' => $eq->id,
                    'price' => (float) $eq->price ?? 0,
                    'qty' => 0
                ];
            }
            $lastCatRef['equipment'][$name]['qty'] += $eq->pivot->quantity;

            $itemCost = $lastCatRef['equipment'][$name]['price'] * $eq->pivot->quantity;
            if ($eq->is_consumable) {
                $materialsCost += $itemCost;
                $matDetails[] = ['name' => $eq->name, 'price' => $lastCatRef['equipment'][$name]['price'], 'qty' => $eq->pivot->quantity];
            } else {
                $equipmentCost += $itemCost;
                $eqDetails[] = ['name' => $eq->name, 'price' => $lastCatRef['equipment'][$name]['price'], 'qty' => $eq->pivot->quantity];
            }
        }

        // Работа сотрудников (общая для проекта)
        $intervals = WorkInterval::where('project_id', $this->project_id)
            ->where('type', 'busy')
            ->get()
            ->groupBy('employee_id');

        $staffDetails = [];
        foreach ($intervals as $empId => $ivs) {
            $minutes = 0;
            $projectRate = null;
            $hourRate = null;
            foreach ($ivs as $iv) {
                $s = strtotime($iv->start_time);
                $e = strtotime($iv->end_time);
                if ($e > $s) {
                    $minutes += ($e - $s) / 60;
                }
                if (!is_null($iv->project_rate)) {
                    $projectRate = (float) $iv->project_rate;
                }
                if (is_null($hourRate) && !is_null($iv->hour_rate)) {
                    $hourRate = (float) $iv->hour_rate;
                }
            }
            $sum = $projectRate ?? ($hourRate * ($minutes / 60) ?? 0);
            $staffCost += $sum;
            $emp = User::find($empId);
            $staffDetails[] = [
                'name' => $emp->name ?? 'Неизвестный',
                'sum' => $sum,
                'rate_type' => $projectRate ? 'project' : 'hour',
                'rate' => $projectRate ?? $hourRate,
                'minutes' => $minutes,
            ];
        }

        // Скидки (если клиент есть)
        $client = $this->client()->first();
        $discEq = $client ? (float) $client->discount_equipment / 100 : 0;
        $discMat = $client ? (float) $client->discount_materials / 100 : 0;
        $discServ = $client ? (float) $client->discount_services / 100 : 0;

        $eqAfterDisc = $equipmentCost * (1 - $discEq);
        $matAfterDisc = $materialsCost * (1 - $discMat);
        // Услуги: работа + доставка, скидка на services
        $servicesCost = $staffCost + $deliveryCost;
        $servAfterDisc = $servicesCost * (1 - $discServ);

        $subtotal = $eqAfterDisc + $matAfterDisc + $servAfterDisc;

        // Налог (если компания есть)
        $company = $this->company()->first();
        $taxData = $company ? $company->calculateTax($subtotal) : ['base' => $subtotal, 'tax' => 0, 'payable' => $subtotal];

        return [
            'equipment' => ['total' => $equipmentCost, 'after_disc' => $eqAfterDisc, 'discount' => $discEq * 100, 'details' => $eqDetails, 'tree' => $eqTree],
            'materials' => ['total' => $materialsCost, 'after_disc' => $matAfterDisc, 'discount' => $discMat * 100, 'details' => $matDetails],
            'services' => ['total' => $servicesCost, 'after_disc' => $servAfterDisc, 'discount' => $discServ * 100, 'staff' => $staffDetails, 'delivery' => $deliveryCost],
            'subtotal' => $subtotal,
            'tax' => $taxData['tax'],
            'total' => $taxData['payable'],
            'tax_method' => $company ? $company->accounting_method : 'none',
            'client' => $client ? $client->name : null,
            'company' => $company ? $company->name : null,
        ];
    }

    private function getCategoryPath($catId, $path = [])
    {
        $cat = Category::find($catId);
        if (!$cat) return array_reverse($path);
        $path[] = $cat->name;
        return $cat->parent_id ? $this->getCategoryPath($cat->parent_id, $path) : array_reverse($path);
    }
}
