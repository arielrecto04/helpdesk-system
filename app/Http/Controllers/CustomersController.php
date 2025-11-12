<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Department; // <-- Idagdag ito
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
// Tinanggal ko ang 'Hash' at 'Rules' kasi hindi naman kailangan para sa Customer (walang password)
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Arr; // Ginamit ko ito para sa update
use Illuminate\Validation\Rule; // Importante ito para sa unique validation check

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        // Fetch paginated customers
        // Pinalitan ko ang $user ng $customer para malinaw
        $customers = Customer::with(['department', 'position']) // <-- 1. MAHALAGA: Eager load
            ->latest()
            ->paginate(10)
            ->through(fn ($customer) => [
                'id' => $customer->id,
                'name' => $customer->first_name . ' ' . $customer->last_name,
                'first_name' => $customer->first_name,
                'middle_name' => $customer->middle_name,
                'last_name' => $customer->last_name,
                'email' => $customer->email,
                'phone_number' => $customer->phone_number,
                'department_name' => $customer->department?->department_name, 
                'position_name' => $customer->position?->position_title,
                'created_at' => $customer->created_at->toDateTimeString(),
                'updated_at' => $customer->updated_at->toDateTimeString(),
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
        // Inayos ko ang path para consistent. 'Customers' (plural) na folder.
        return Inertia::render('Customer/Create', [
            'departments' => Department::all(['id', 'department_name']), // Palitan kung iba ang name
            'positions' => Position::all(['id', 'position_title']), // Palitan kung iba ang name
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'first_name' => 'required|string|max:100', // Inayos ang max length
            'middle_name' => 'nullable|string|max:100', // Inayos ang max length
            'last_name' => 'required|string|max:100', // Inayos ang max length
            'phone_number' => 'nullable|string|max:20', // Inayos para maging nullable at max:20
            'email' => 'required|string|email|max:255|unique:'.Customer::class,
            'department_id' => 'nullable|exists:departments,id', // Inayos para maging nullable
            'position_id' => 'nullable|exists:positions,id', // Inayos para maging nullable
        ]);

        // Create the new customer
        Customer::create($validated); 

        // Redirect to the customer index page with a success message
        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     * Pinalitan ko ang $user ng $customer para tama.
     */
    public function show(Customer $customer): Response
    {
        $customer->load(['department', 'position']);

        return Inertia::render('Customer/Show', [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->first_name . ' ' . $customer->last_name,
                'first_name' => $customer->first_name,
                'middle_name' => $customer->middle_name,
                'last_name' => $customer->last_name,
                'email' => $customer->email,
                'phone_number' => $customer->phone_number,
                'department_name' => $customer->department?->department_name, 
                'position_name' => $customer->position?->position_title,
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

        return Inertia::render('Customer/Edit', [
            'customer' => [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'middle_name' => $customer->middle_name,
                'last_name' => $customer->last_name,
                'email' => $customer->email,
                'phone_number' => $customer->phone_number,
                'department_id' => $customer->department_id,
                'position_id' => $customer->position_id,
            ],

            'departments' => Department::all(['id', 'department_name']),
            'positions' => Position::all(['id', 'position_title']), 
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Pinalitan ko ang $user ng $customer
     */
    public function update(Request $request, Customer $customer): RedirectResponse
    {
        // Validate the request
        $validated = $request->validate([
            'first_name' => 'required|string|max:100', // Inayos ang max length
            'middle_name' => 'nullable|string|max:100', // Inayos ang max length
            'last_name' => 'required|string|max:100', // Inayos ang max length
            'phone_number' => 'nullable|string|max:20', // Inayos para maging nullable at max:20
            'department_id' => 'nullable|exists:departments,id', // Inayos para maging nullable
            'position_id' => 'nullable|exists:positions,id', // Inayos para maging nullable
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('customers')->ignore($customer->id),
            ],
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * Pinalitan ko ang $customers ng $customer
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        // Delete the customer
        $customer->delete();

        // Inayos ko ang success message
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}