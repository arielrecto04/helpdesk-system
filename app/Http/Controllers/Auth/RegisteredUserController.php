<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Role;
use App\Models\Customer;
use App\Models\Company;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        $companies = Company::orderBy('name')->pluck('name');

        return Inertia::render('Auth/Register', [
            'companies' => $companies,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'company' => 'nullable|string|max:255',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Auto-assign the 'customer' role to newly registered users (case-insensitive)
        $customerRole = Role::whereRaw('LOWER(name) = ?', ['customer'])->first();
        if ($customerRole) {
            $user->roles()->syncWithoutDetaching([$customerRole->id]);
        }

        // Ensure there's a Customer record for this user and link it
        $customer = null;
        if ($user->email) {
            $customer = Customer::where('email', $user->email)->first();
        }

        // Handle company linking/creation if provided
        $companyModel = null;
        $companyName = $request->input('company');
        if ($companyName) {
            $companyModel = Company::whereRaw('LOWER(name) = ?', [strtolower($companyName)])->first();
            if (! $companyModel) {
                $companyModel = Company::create([
                    'name' => $companyName,
                ]);
            }
        }

        if ($customer) {
            $customer->user_id = $user->id;
            $customer->first_name = $customer->first_name ?: $user->first_name;
            $customer->middle_name = $customer->middle_name ?: $user->middle_name;
            $customer->last_name = $customer->last_name ?: $user->last_name;
            if ($companyModel) {
                $customer->company_id = $companyModel->id;
            }
            $customer->save();
        } else {
            Customer::create([
                'user_id' => $user->id,
                'first_name' => $user->first_name ?? null,
                'middle_name' => $user->middle_name ?? null,
                'last_name' => $user->last_name ?? null,
                'email' => $user->email ?? null,
                'company_id' => $companyModel ? $companyModel->id : null,
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        // Send newly registered customers to the customer dashboard
        return redirect()->route('customer.dashboard');
    }
}
