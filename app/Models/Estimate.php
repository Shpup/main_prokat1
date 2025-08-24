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
            ->withPivot('status', 'quantity', 'coefficient', 'discount')
            ->withTimestamps();
    }

    public function attachEquipment($equipmentId, $quantity = 1, $status = 'assigned', $coefficient = 1.0, $discount = 0)
    {
        $equipment = Equipment::findOrFail($equipmentId);

        // Проверка конфликтов только если даты проекта заданы
        $conflicts = false;
        if ($this->project->start_date && $this->project->end_date) {
            $conflicts = DB::table('estimate_equipment')
                ->join('estimates', 'estimate_equipment.estimate_id', '=', 'estimates.id')
                ->join('projects', 'estimates.project_id', '=', 'projects.id')
                ->where('estimate_equipment.equipment_id', $equipmentId)
                ->where('estimate_equipment.status', 'assigned')
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

        // Attach или update
        $existing = $this->equipment()->where('equipment_id', $equipmentId)->first();
        if ($existing) {
            $this->equipment()->updateExistingPivot($equipmentId, [
                'quantity' => $existing->pivot->quantity + $quantity,
                'status' => $status,
                'coefficient' => $coefficient,
                'discount' => $discount
            ]);
        } else {
            $this->equipment()->attach($equipmentId, [
                'quantity' => $quantity,
                'status' => $status,
                'coefficient' => $coefficient,
                'discount' => $discount
            ]);
        }
    }

    public function detachEquipment($equipmentId)
    {
        $this->equipment()->detach($equipmentId);
    }

    public function updateStaff($employeeId, $field, $value)
    {
        $intervals = WorkInterval::where('project_id', $this->project_id)
            ->where('employee_id', $employeeId)
            ->where('type', 'busy')
            ->get();

        if ($intervals->isEmpty()) {
            throw new \Exception('Рабочие интервалы для сотрудника не найдены.');
        }

        foreach ($intervals as $interval) {
            if ($field === 'rate') {
                $interval->hour_rate = $value;
            } elseif ($field === 'coefficient') {
                $interval->coefficient = $value;
            } elseif ($field === 'discount') {
                $interval->discount = $value;
            }
            $interval->save();
        }
    }

    public function getEstimate()
    {
        $equipmentCost = 0;
        $materialsCost = 0;
        $staffCost = 0;
        $deliveryCost = (float) ($this->delivery_cost ?? 0);

        $eqTree = [];
        $matTree = [];
        $eqDetails = [];
        $matDetails = [];

        $equipments = $this->equipment()->withoutGlobalScope('admin')->get();

        foreach ($equipments as $eq) {

            $path = $this->getCategoryPath($eq->category_id);
            if (empty($path)) {
                $path = ['Без категории'];
            }

            $tree = &$eqTree;
            if ($eq->is_consumable) {
                $tree = &$matTree;
            }

            $currentLevel = &$tree;
            foreach ($path as $catName) {
                $catName = trim($catName);
                if (!isset($currentLevel[$catName])) {
                    $currentLevel[$catName] = ['sub' => [], 'equipment' => []];
                }
                $currentLevel = &$currentLevel[$catName]['sub'];
            }

            $parentLevel = &$tree;
            foreach ($path as $index => $catName) {
                $catName = trim($catName);
                $eqKey = $eq->id . '-' . $eq->name;
                if (!isset($parentLevel[$catName]['equipment'][$eqKey])) {
                    $parentLevel[$catName]['equipment'][$eqKey] = [
                        'id' => $eq->id,
                        'name' => $eq->name,
                        'price' => (float) ($eq->price ?? 0),
                        'qty' => (float) ($eq->pivot->quantity ?? 1),
                        'coefficient' => (float) ($eq->pivot->coefficient ?? 1.0),
                        'discount' => (float) ($eq->pivot->discount ?? 0),
                        'is_consumable' => $eq->is_consumable
                    ];
                } else {
                    $parentLevel[$catName]['equipment'][$eqKey]['qty'] += $eq->pivot->quantity;
                }
                $parentLevel = &$parentLevel[$catName]['sub'];
            }

            $itemCost = ($eq->price ?? 0) * ($eq->pivot->coefficient ?? 1.0) * $eq->pivot->quantity * (1 - (($eq->pivot->discount ?? 0) / 100));
            if ($eq->is_consumable) {
                $materialsCost += $itemCost;
                $matDetails[] = [
                    'name' => $eq->name,
                    'price' => (float) ($eq->price ?? 0),
                    'qty' => $eq->pivot->quantity,
                    'coefficient' => (float) ($eq->pivot->coefficient ?? 1.0),
                    'discount' => (float) ($eq->pivot->discount ?? 0)
                ];
            } else {
                $equipmentCost += $itemCost;
                $eqDetails[] = [
                    'name' => $eq->name,
                    'price' => (float) ($eq->price ?? 0),
                    'qty' => $eq->pivot->quantity,
                    'coefficient' => (float) ($eq->pivot->coefficient ?? 1.0),
                    'discount' => (float) ($eq->pivot->discount ?? 0)
                ];
            }
        }

        $intervals = WorkInterval::where('project_id', $this->project_id)
            ->where('type', 'busy')
            ->get()
            ->groupBy('employee_id');

        $staffDetails = [];
        foreach ($intervals as $empId => $ivs) {
            $minutes = 0;
            $projectRate = null;
            $hourRate = null;
            $coefficient = 1.0;
            $discount = 0.0;
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
                if (!is_null($iv->coefficient)) {
                    $coefficient = (float) $iv->coefficient;
                }
                if (!is_null($iv->discount)) {
                    $discount = (float) $iv->discount;
                }
            }
            $sum = $projectRate ?? ($hourRate * ($minutes / 60) * $coefficient * (1 - ($discount / 100)) ?? 0);
            $staffCost += $sum;
            $emp = User::find($empId);
            $staffDetails[] = [
                'id' => $empId,
                'name' => $emp->name ?? 'Неизвестный',
                'sum' => $sum,
                'rate_type' => $projectRate ? 'project' : 'hour',
                'rate' => $projectRate ?? $hourRate,
                'minutes' => $minutes,
                'coefficient' => $coefficient,
                'discount' => $discount
            ];
        }

        $client = $this->project->client()->first();
        $discEq = $client ? (float) $client->discount_equipment / 100 : 0;
        $discMat = $client ? (float) $client->discount_materials / 100 : 0;
        $discServ = $client ? (float) $client->discount_services / 100 : 0;

        $eqAfterDisc = $equipmentCost * (1 - $discEq);
        $matAfterDisc = $materialsCost * (1 - $discMat);
        $servicesCost = $staffCost + $deliveryCost;
        $servAfterDisc = $servicesCost * (1 - $discServ);

        $subtotal = $eqAfterDisc + $matAfterDisc + $servAfterDisc;

        $company = $this->company()->first();
        $taxData = $company ? $company->calculateTax($subtotal) : ['base' => $subtotal, 'tax' => 0, 'payable' => $subtotal];

        return [
            'equipment' => ['total' => $equipmentCost, 'after_disc' => $eqAfterDisc, 'discount' => $discEq * 100, 'details' => $eqDetails, 'tree' => $eqTree],
            'materials' => ['total' => $materialsCost, 'after_disc' => $matAfterDisc, 'discount' => $discMat * 100, 'details' => $matDetails, 'tree' => $matTree],
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
