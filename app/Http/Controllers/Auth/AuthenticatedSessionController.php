<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Redirect based on role/permission after authentication
        /** @var User|null $user */
        $user = Auth::user();

        if ($user) {
            // If user has explicit customer dashboard permission or role, send to customer portal
            $hasCustomerDash = method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('view_customer_dashboard');

            $isCustomerRole = method_exists($user, 'hasRole') && $user->hasRole('Customer');

            if ($hasCustomerDash || $isCustomerRole) {
                // Always send customers to the customer dashboard after login
                return redirect()->route('customer.dashboard');
            }
        }

        // Fallback to staff dashboard
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
