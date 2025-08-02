<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Проверяет наличие активной подписки, исключая определенные маршруты.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Исключаем маршруты аутентификации и оплаты
        $excludedRoutes = ['login', 'register', 'subscription.payment', 'subscription.activate', 'logout'];
        if (Auth::check() && !in_array($request->route()->getName(), $excludedRoutes)) {
            if (!Auth::user()->hasActiveSubscription()) {
                return redirect()->route('subscription.payment');
            }
        }

        return $next($request);
    }
}
