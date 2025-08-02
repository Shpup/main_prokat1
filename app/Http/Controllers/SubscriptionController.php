<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SubscriptionController extends Controller
{
    /**
     * Отображает страницу оплаты подписки.
     */
    public function showPaymentPage(): View
    {
        return view('subscription.payment');
    }

    /**
     * Имитация активации подписки (для тестирования).
     */
    public function activate(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $user->subscription()->updateOrCreate(
            ['user_id' => $user->id],
            ['is_active' => true, 'expires_at' => now()->addYear()]
        );
        return redirect()->route('dashboard')->with('success', 'Подписка активирована.');
    }
}
