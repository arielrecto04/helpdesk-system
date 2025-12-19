<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Company;
use App\Models\Department; 
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Arr; 
use Illuminate\Validation\Rule; 

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        
        $customers = Customer::latest()
            ->paginate(10)
            ->through(fn ($customer) => [
                'id' => $customer->id,
                'name' => $customer->first_name . ' ' . $customer->last_name,
                'first_name' => $customer->first_name,
                'middle_name' => $customer->middle_name,
                'last_name' => $customer->last_name,
                'email' => $customer->email,
                'phone_number' => $customer->phone_number,
                'company_id' => $customer->company_id ?? null,
                'company_name' => $customer->company?->name ?? null,
                'created_at' => $customer->created_at->toDateTimeString(),
                'updated_at' => $customer->updated_at->toDateTimeString(),
                'has_account' => $customer->user_id,
                
            ]);

        return Inertia::render('Customer', [
            'customers' => $customers,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {

        $companies = Company::select('id', 'name')->get();

        return Inertia::render('Customer/Create', [
            'companies' => $companies,
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
            'email' => 'required|string|email|max:255|unique:'.Customer::class,
            'company_id' => 'nullable|exists:companies,id',
        ]);

        Customer::create($validated);
        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     * Pinalitan ko ang $user ng $customer para tama.
     */
    public function show(Customer $customer): Response
    {
        $hasAccount = \App\Models\User::where('email', $customer->email)->exists();

        return Inertia::render('Customer/Show', [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->first_name . ' ' . $customer->last_name,
                'first_name' => $customer->first_name,
                'middle_name' => $customer->middle_name,
                'last_name' => $customer->last_name,
                'email' => $customer->email,
                'phone_number' => $customer->phone_number,
                'has_account' => $hasAccount,
                'company_id' => $customer->company_id ?? null,
                'company_name' => $customer->company?->name ?? null,
                'created_at' => $customer->created_at->toDateTimeString(),
                'updated_at' => $customer->updated_at->toDateTimeString(),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     * Pinalitan ko ang $user ng $customer
     */
    public function edit(Customer $customer): Response
    {

        $companies = Company::select('id', 'name')->get();

        return Inertia::render('Customer/Edit', [
            'customer' => [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'middle_name' => $customer->middle_name,
                'last_name' => $customer->last_name,
                'email' => $customer->email,
                'phone_number' => $customer->phone_number,
                'company_id' => $customer->company_id ?? null,
                'company_name' => $customer->company?->name ?? null,
            ],
            'companies' => $companies,
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Pinalitan ko ang $user ng $customer
     */
    
    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('customers')->ignore($customer->id),
            ],
            'company_id' => 'nullable|exists:companies,id',
        ]);

        $customer->update($validated);
        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}