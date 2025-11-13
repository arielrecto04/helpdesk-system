<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\Rule;

class PositionsController extends Controller
{
    /**
     * Display a listing of the resource (Hindi pa natin ginagamit).
     */
    public function index(): Response
    {
        return Inertia::render('Position/Index', [
            'positions' => Position::with('department')->latest()->paginate(10)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * Ito ang kailangan para sa 'Create' button mo.
     */
    public function create(Request $request): Response
    {
        $department_id = $request->query('department');
        
        return Inertia::render('Positions/Create', [
            'department_id' => $department_id,
            'departments' => Department::all(['id', 'department_name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'position_title' => [
                'required',
                'string',
                'max:100',
                Rule::unique('positions')->where(function ($query) use ($request) {
                    return $query->where('department_id', $request->department_id);
                }),
            ],
        ]);

        Position::create($validated);
        return redirect()->route('departments.show', $validated['department_id'])
                         ->with('success', 'Position created successfully.');
    }

    /**
     * Display the specified resource.
     */
    // public function show(Position $position): Response
    // {
    //     return Inertia::render('Position/Show', [
    //         'position' => $position->load('department')
    //     ]);
    // }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Position $position): Response
    {
        return Inertia::render('Positions/Edit', [
            'position' => $position,
            'departments' => Department::all(['id', 'department_name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Position $position): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'position_title' => [
                'required',
                'string',
                'max:100',
                Rule::unique('positions')->where(function ($query) use ($request) {
                    return $query->where('department_id', $request->department_id);
                })->ignore($position->id),
            ],
        ]);

        $position->update($validated);
        return redirect()->route('departments.show', $validated['department_id'])
                         ->with('success', 'Position updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Position $position): RedirectResponse
    {

        $department_id = $position->department_id;
        
        $position->delete();

        return redirect()->route('departments.show', $department_id)
                         ->with('success', 'Position deleted successfully.');
    }
}