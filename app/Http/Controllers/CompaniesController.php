<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CompaniesController extends Controller
{

    public function index(): Response
    {
        return Inertia::render('Company', [
            'companies' => Company::all(),
        ]);
    }


    public function create(Request $request): Response
    {
        return Inertia::render('Company/Create', [
            'name' => $request->query('name'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(Company::class)],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique(Company::class)],
        ]);
        Company::create($validated);

        return redirect()->route('companies.index')->with('message', 'Company created successfully.');
    }


    public function edit(Company $company): Response
    {
        return Inertia::render('Company/Edit', [
            'company' => $company
        ]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(Company::class)->ignore($company->id)],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique(Company::class)->ignore($company->id)],
        ]);
        $company->update($validated);

        return redirect()->route('companies.index')->with('message', 'Company updated successfully.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();
        return redirect()->route('companies.index')->with('message', 'Company deleted successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param Company $company
     * @return Response
     */
    public function show(Company $company): Response
    {
        return Inertia::render('Company/Show', [
            'company' => $company,
        ]);
    }

}