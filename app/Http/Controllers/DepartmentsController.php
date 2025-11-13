<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\Rule; 

class DepartmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $departments = Department::orderBy('department_name', 'asc')->paginate(10)->through(fn ($dept) => [
            'id' => $dept->id,
            'department_name' => $dept->department_name
        ]);

        return Inertia::render('Department', [
            'departments' => $departments, 
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Departments/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department_name' => 'required|string|max:255|unique:departments,department_name',
        ]);

        Department::create($validated);

        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */

    public function edit(Department $department): Response 
    {
        return Inertia::render('Departments/Edit', [
            'department' => $department,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, Department $department): RedirectResponse
    {
        $validated = $request->validate([

            'department_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'department_name')->ignore($department->id),
            ],
        ]);
        $department->update($validated);

        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department): RedirectResponse
    {
        $department->delete();
        return redirect()->route('departments.index')->with('success', 'Department deleted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department): Response
    {
        $positions = $department->positions()
                                ->orderBy('position_title')
                                ->paginate(10)
                                ->through(fn($pos) => [
                                    'id' => $pos->id,
                                    'position_title' => $pos->position_title,
                                ]);

        return Inertia::render('Department/Show', [
            'department' => $department,
            'positions' => $positions,
        ]);
    }
}