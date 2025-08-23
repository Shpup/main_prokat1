<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EquipmentController extends Controller
{
    public function updateStatus(Request $request)
    {
        // Валидация входных данных
        $request->validate([
            'id' => 'required|integer|exists:equipments,id',
            'action' => 'required|in:send,accept',
        ]);

        // Проверка аутентификации
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Неавторизованный доступ',
            ], 401);
        }

        // Поиск оборудования
        $equipment = Equipment::find($request->id);
        if (!$equipment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Оборудование не найдено',
            ], 404);
        }

        $action = $request->action;
        $currentStatus = $equipment->status;

        // Логика изменения статуса
        if ($action === 'send') {
            if ($currentStatus === 'on_warehouse') {
                $equipment->status = 'sent_to_project';
                $equipment->save();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Статус изменен на "отправлен на проект"',
                    'equipment' => $equipment,
                ], 200);
            } elseif ($currentStatus === 'on_project') {
                $equipment->status = 'sent_to_warehouse';
                $equipment->save();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Статус изменен на "отправлен на склад"',
                    'equipment' => $equipment,
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Уже в пути',
                ], 400);
            }
        } elseif ($action === 'accept') {
            if ($currentStatus === 'sent_to_project') {
                $equipment->status = 'on_project';
                $equipment->save();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Статус изменен на "на проекте"',
                    'equipment' => $equipment,
                ], 200);
            } elseif ($currentStatus === 'sent_to_warehouse') {
                $equipment->status = 'on_warehouse';
                $equipment->save();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Статус изменен на "на складе"',
                    'equipment' => $equipment,
                ], 200);
            } else {
                $message = $currentStatus === 'on_warehouse' ? 'Уже на складе' : 'Уже на проекте';
                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ], 400);
            }
        }
    }
}
