<?php

namespace App\Http\Controllers;

use App\Models\HelpdeskTeam;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class HelpdeskTeamsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('HelpdeskTeams', [
            'teams' => HelpdeskTeam::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('HelpdeskTeams/Create', [
            'team_name' => $request->query('team_name'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate the incoming request
        $validated = $request->validate([
            'team_name' => 'required|string|max:255|unique:helpdesk_teams',
        ]);

        // Create the new team
        HelpdeskTeam::create($validated);

        // Redirect back to the index page
        return redirect()->route('helpdeskteams.index')->with('message', 'Team created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     * We use route-model binding here to automatically find the team.
     */
    public function edit(HelpdeskTeam $team): Response
    {
        return Inertia::render('HelpdeskTeams/Edit', [
            'team' => $team
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HelpdeskTeam $team): RedirectResponse
    {
        // Validate the incoming request
        $validated = $request->validate([
            'team_name' => 'required|string|max:255|unique:helpdesk_teams,team_name,' . $team->id,
        ]);

        // Update the team
        $team->update($validated);

        // Redirect back to the index page
        return redirect()->route('helpdeskteams.index')->with('message', 'Team updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HelpdeskTeam $team): RedirectResponse
    {
        $team->delete();
        return redirect()->route('helpdeskteams.index')->with('message', 'Team deleted successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param HelpdeskTeam $team
     * @return Response
     */
    public function show(HelpdeskTeam $team): Response
    {
        return Inertia::render('HelpdeskTeams/Show', [
            'helpdesk_team' => $team,
        ]);
    }

}