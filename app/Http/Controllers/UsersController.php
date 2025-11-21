<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules; 
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $users = User::with('roles')->latest()->paginate(10)->through(fn ($user) => [
            'id' => $user->id,
            'name' => $user->first_name . ' ' . $user->last_name,
            'email' => $user->email,
            'roles' => $user->roles->map(fn ($role) => ['id' => $role->id, 'name' => $role->name]),
            'created_at' => $user->created_at->toDateTimeString(),
            'updated_at' => $user->updated_at->toDateTimeString(),
        ]);

        return Inertia::render('Users', [
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {

        $customerId = request()->query('customer');

        if ($customerId) {
            $customer = \App\Models\Customer::find($customerId);
            if ($customer) {
                $prefill = [
                    'first_name' => $customer->first_name,
                    'middle_name' => $customer->middle_name,
                    'last_name' => $customer->last_name,
                    'email' => $customer->email,
                    'customer_id' => $customer->id,
                ];

                return Inertia::render('Users/Create', [
                    'prefill' => $prefill,
                    'is_customer' => true,
                    'roles' => Role::all(['id', 'name']),
                ]);
            }
        }

        return Inertia::render('Users/Create', [
            'roles' => Role::all(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => 'sometimes|array',
            'roles.*' => 'exists:roles,id',
            'customer_id' => 'sometimes|nullable|exists:customers,id',
        ]);
        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'middle_name' => $validated['middle_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // If roles are provided (e.g., employee flow), assign them
        if (!empty($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }

        // If created for a customer, ensure 'customer' role exists and assign it
        if (!empty($validated['customer_id'])) {
            $customerRole = \App\Models\Role::firstOrCreate(
                ['name' => 'customer'],
                ['description' => 'Customer role']
            );
            if ($customerRole) {
                $user->roles()->syncWithoutDetaching([$customerRole->id]);
            }
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     * We use Route-Model Binding (User $user) to automatically find the user.
     */
    public function show(User $user): Response
    {
        $user->load('roles');
        return Inertia::render('Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'first_name' => $user->first_name,
                'middle_name' => $user->middle_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'roles' => $user->roles->map(fn ($role) => ['id' => $role->id, 'name' => $role->name]),
                'created_at' => $user->created_at->toDateTimeString(),
                'updated_at' => $user->updated_at->toDateTimeString(),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): Response
    {
        $user->load('roles');
        return Inertia::render('Users/Edit', [
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'middle_name' => $user->middle_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('id'),
            ],
            'roles' => Role::all(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        // Validate the request
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'roles' => 'sometimes|array',
        ]);

        $user->fill(Arr::except($validated, ['password', 'roles']));

        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if (isset($validated['roles'])) {
            $user->roles()->sync($validated['roles']);
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}