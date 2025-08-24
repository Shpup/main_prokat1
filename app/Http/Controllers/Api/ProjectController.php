<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function getUserProjects(Request $request)
    {
        // Проверка аутентификации
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Неавторизованный доступ',
            ], 401);
        }

        $user = Auth::user();
        $query = Project::query()
            ->select('id', 'name', 'description', 'start_date', 'end_date','status')
            ->whereIn('status', ['new', 'active']);

        // Фильтрация проектов в зависимости от роли
        if ($user->hasRole('admin')) {
            $query->where('admin_id', $user->id);
        } elseif ($user->hasRole('manager')) {
            $query->where('manager_id', $user->id);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'У пользователя недостаточно прав',
            ], 403);
        }

        $projects = $query->get();

        return response()->json([
            'status' => 'success',
            'projects' => $projects,
        ], 200);
    }
}
