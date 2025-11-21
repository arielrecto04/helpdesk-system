<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Department; 
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Arr; 
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB; // <-- IDAGDAG ITO
use Illuminate\Support\Facades\Hash; // <-- IDAGDAG ITO
use Illuminate\Validation\Rules; // <-- IDAGDAG ITO
use App\Models\Role;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $employees = Employee::with(['department', 'position']) 
            ->latest()
            ->paginate(10)
            ->through(fn ($employee) => [
                'id' => $employee->id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'first_name' => $employee->first_name,
                'middle_name' => $employee->middle_name,
                'last_name' => $employee->last_name,
                'email' => $employee->email,
                'phone_number' => $employee->phone_number,
                'department_name' => $employee->department?->department_name, 
                'position_name' => $employee->position?->position_title,
                'employee_code' => $employee->employee_code,
                'hire_date' => $employee->hire_date->toDateString(),
                'created_at' => $employee->created_at->toDateTimeString(),
                'updated_at' => $employee->updated_at->toDateTimeString(),
            ]);

        return Inertia::render('Employee', [
            'employees' => $employees,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */

    public function create(): Response
    {

        return Inertia::render('Employee/Create', [
            'departments' => Department::all(['id', 'department_name']),
            'positions' => Position::all(['id', 'position_title', 'department_id']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'required|string|email|max:255|unique:'.Employee::class,
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'employee_code' => 'nullable|string|max:20|unique:'.Employee::class,
            'hire_date' => 'nullable|date',
        ]);

        Employee::create($validated); 
        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    /**
     * Display the specified resource.
     * Pinalitan ko ang $user ng $employee para tama.
     */
    public function show(Employee $employee): Response
    {
        $employee->load(['department', 'position']);

        $hasAccount = User::where('email', $employee->email)->exists();

        return Inertia::render('Employee/Show', [
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'first_name' => $employee->first_name,
                'middle_name' => $employee->middle_name,
                'last_name' => $employee->last_name,
                'email' => $employee->email,
                'phone_number' => $employee->phone_number,
                'department_name' => $employee->department?->department_name, 
                'position_name' => $employee->position?->position_title,
                'employee_code' => $employee->employee_code,
                'hire_date' => $employee->hire_date->toDateString(),
                'created_at' => $employee->created_at->toDateTimeString(),
                'updated_at' => $employee->updated_at->toDateTimeString(),
                'user_id' => $employee->user_id,
                'has_account' => $hasAccount,
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     * Pinalitan ko ang $user ng $employee
     */
    public function edit(Employee $employee): Response
    {

        return Inertia::render('Employee/Edit', [
            'employee' => [
                'id' => $employee->id,
                'first_name' => $employee->first_name,
                'middle_name' => $employee->middle_name,
                'last_name' => $employee->last_name,
                'email' => $employee->email,
                'phone_number' => $employee->phone_number,
                'department_id' => $employee->department_id,
                'position_id' => $employee->position_id,
                'employee_code' => $employee->employee_code,
                'hire_date' => $employee->hire_date?->toDateString(),
            ],
            'departments' => Department::all(['id', 'department_name']),
            'positions' => Position::all(['id', 'position_title', 'department_id']), 
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Pinalitan ko ang $user ng $employee
     */
    
    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('employees')->ignore($employee->id),
            ],
            'employee_code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('employees')->ignore($employee->id),
            ],
            'hire_date' => 'nullable|date',
        ]);
        $employee->update($validated);
        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }


    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }

    // ____________________________________________________

    public function createAccount(Employee $employee): Response|RedirectResponse
    {

        if ($employee->user_id) {

            return redirect()->route('employees.show', $employee->id)->with('error', 'This employee already has an account.');
        }
        
        $prefill = [
            'first_name' => $employee->first_name,
            'middle_name' => $employee->middle_name,
            'last_name' => $employee->last_name,
            'email' => $employee->email,
            'employee_id' => $employee->id,
        ];

        return Inertia::render('Users/Create', [
            'prefill' => $prefill,
            'roles' => Role::all(['id', 'name']),
        ]);
    }
    
    public function storeAccount(Request $request, Employee $employee): RedirectResponse
    {
        if ($employee->user_id) {
            return redirect()->route('employees.show', $employee->id)->with('error', 'This employee already has an account.');
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'email' => 'required|email',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);
        
        if ($employee->email !== $validated['email']) {
             return back()->with('error', 'Email mismatch. Please contact support.');
        }
        
        if (User::where('email', $employee->email)->exists()) {
             return back()->withErrors(['email' => 'This email address is already taken by another user.']);
        }

        DB::transaction(function () use ($validated, $employee) {
            $user = User::create([
                'first_name' => $employee->first_name,
                'middle_name' => $employee->middle_name,
                'last_name' => $employee->last_name,
                'email' => $employee->email,
                'password' => Hash::make($validated['password']),
            ]);

            // assign roles if provided
            if (!empty($validated['roles'])) {
                $user->roles()->sync($validated['roles']);
            }

            $employee->user_id = $user->id;
            $employee->save();
        });

        return redirect()->route('employees.show', $employee->id)
                         ->with('success', 'User account created and linked successfully.');
    }
}