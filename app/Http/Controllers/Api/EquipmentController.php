<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\Estimate;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EquipmentController extends Controller
{
    public function updateStatus(Request $request)
    {
        // Валидация входных данных
        $request->validate([
            'equipment_id' => 'required|integer|exists:equipment,id',
            'project_id' => 'required|integer|exists:projects,id',
            'action' => 'required|in:send,accept',
        ]);

        // Проверка аутентификации
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Неавторизованный доступ',
            ], 401);
        }

        $user = Auth::user();
        $equipment = Equipment::find($request->equipment_id);
        $project = Project::find($request->project_id);

        // Проверка существования оборудования и проекта
        if (!$equipment || !$project) {
            return response()->json([
                'status' => 'error',
                'message' => 'Оборудование или проект не найдены',
            ], 404);
        }

        // Проверка статуса проекта
        if (!in_array($project->status, ['new', 'active'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Проект неактивен',
            ], 400);
        }

        // Проверка роли пользователя
        if ($user->hasRole('manager') && $project->manager_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'У вас нет прав для изменения статуса в этом проекте',
            ], 403);
        } elseif ($user->hasRole('admin') && $project->admin_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'У вас нет прав для изменения статуса в этом проекте',
            ], 403);
        } elseif (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Недостаточно прав',
            ], 403);
        }

        // Проверка привязки оборудования к проекту через смету
        $estimateEquipment = DB::table('estimate_equipment')
            ->join('estimates', 'estimate_equipment.estimate_id', '=', 'estimates.id')
            ->where('estimates.project_id', $request->project_id)
            ->where('estimate_equipment.equipment_id', $request->equipment_id)
            ->first();

        if (!$estimateEquipment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Оборудование не привязано к указанному проекту',
            ], 400);
        }

        $action = $request->action;
        $currentStatus = $equipment->status;

        // Соответствие статусов для estimate_equipment
        $estimateStatusMap = [
            'on_warehouse' => 'on_stock',
            'sent_to_project' => 'assigned',
            'on_project' => 'used',
            'sent_to_warehouse' => 'on_stock',
        ];

        // Логика изменения статуса
        if ($action === 'send') {
            if ($currentStatus === 'on_warehouse') {
                $equipment->status = 'sent_to_project';
                $newEstimateStatus = 'sent_to_project';
            } elseif ($currentStatus === 'on_project') {
                $equipment->status = 'sent_to_warehouse';
                $newEstimateStatus = 'sent_to_warehouse';
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Уже в пути',
                ], 400);
            }
        } elseif ($action === 'accept') {
            if ($currentStatus === 'sent_to_project') {
                $equipment->status = 'on_project';
                $newEstimateStatus = 'on_project';
            } elseif ($currentStatus === 'sent_to_warehouse') {
                $equipment->status = 'on_warehouse';
                $newEstimateStatus = 'on_warehouse';
            } else {
                $message = $currentStatus === 'on_warehouse' ? 'Уже на складе' : 'Уже на проекте';
                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ], 400);
            }
        }

        // Обновление статуса в equipment
        $equipment->save();

        // Обновление статуса в estimate_equipment для всех смет в указанном проекте
        DB::table('estimate_equipment')
            ->join('estimates', 'estimate_equipment.estimate_id', '=', 'estimates.id')
            ->where('estimates.project_id', $request->project_id)
            ->where('estimate_equipment.equipment_id', $request->equipment_id)
            ->update(['estimate_equipment.status' => $newEstimateStatus]);

        return response()->json([
            'status' => 'success',
            'message' => 'Статус изменен на "' . $this->getStatusMessage($equipment->status) . '"',
        ], 200);
    }

    private function getStatusMessage($status)
    {
        $messages = [
            'on_warehouse' => 'на складе',
            'sent_to_project' => 'отправлен на проект',
            'on_project' => 'на проекте',
            'sent_to_warehouse' => 'отправлен на склад',
        ];

        return $messages[$status] ?? $status;
    }
}
