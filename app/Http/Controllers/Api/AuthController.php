<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Валидация входных данных
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // Попытка аутентификации
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            // Создание токена (предполагается использование Laravel Sanctum)
            $token = $user->createToken('MobileApp')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'user' => [
                    'name' => $user->name,
                    'role' => $user->role, // Предполагается, что в модели User есть поле role
                ],
                'token' => $token,
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Неверные учетные данные',
        ], 401);
    }
}
