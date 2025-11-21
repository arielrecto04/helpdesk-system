<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\HelpdeskTeam;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Database\Eloquent\Builder;

class TeamTicketsController extends Controller
{
    /**
     * Display a paginated list of tickets for teams the user can access.
     * If `team` query param is present, it will filter by that team (and check access).
     */
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = Auth::user();

        $isAdmin = $user->hasRole('admin');

        $query = Ticket::query()->with(['customer', 'team', 'assignedTo'])->latest();

        if ($request->filled('team')) {
            $teamId = $request->input('team');
            $team = HelpdeskTeam::findOrFail($teamId);

            if (!$isAdmin) {
                $hasTeamAccess = $user->teams()->where('helpdesk_teams.id', $teamId)->exists();

                $hasPermissionAccess = $user->hasPermissionTo('view_team_tickets')
                    || $user->hasPermissionTo('view_all_tickets')
                    || $user->hasPermissionTo('view_tickets')
                    || $user->hasPermissionTo('can_view_other_teams_tickets')
                    || $user->hasPermissionTo('can_view_other_locations_tickets')
                    || $user->hasPermissionTo('can_view_other_users_tickets');

                if (!$hasTeamAccess && !$hasPermissionAccess) {
                    abort(403, 'Unauthorized. You can only view tickets from your teams.');
                }
            }

            $query->where('team_id', $teamId);
        } else {
            if (!$isAdmin) {
                $teamIds = $user->teams()->pluck('helpdesk_teams.id')->toArray();
                $query->whereIn('team_id', $teamIds);
            }
        }

        $tickets = $query->paginate(10)->withQueryString();

        $tickets->getCollection()->transform(function ($ticket) {
            if ($ticket->assignedTo) {
                $ticket->assigned_user_name = $ticket->assignedTo->first_name . ' ' . $ticket->assignedTo->last_name;
            } else {
                $ticket->assigned_user_name = null;
            }
            return $ticket;
        });

        return Inertia::render('TeamTickets', [
            'tickets' => $tickets,
            'isAdmin' => $isAdmin,
            'filters' => $request->only(['search', 'status', 'priority', 'team'])
        ]);
    }

    public function create(): Response
    {
        $customers = Customer::latest()->get(['id', 'first_name', 'last_name']);
        $teams = HelpdeskTeam::latest()->get(['id', 'name']);

        return Inertia::render('TeamTickets/Create', [
            'customers' => $customers->map(fn($c) => ['id' => $c->id, 'name' => $c->first_name . ' ' . $c->last_name]),
            'teams' => $teams,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'customer_id' => 'required|exists:customers,id',
            'team_id' => 'required|exists:helpdesk_teams,id',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'priority' => 'nullable|string|max:50',
            'stage' => 'nullable|string|max:50',
            'deadline' => 'nullable|date',
        ]);

        Ticket::create($validated);

        return redirect()->route('teamtickets.index')->with('success', 'Ticket created successfully.');
    }

    public function show(Ticket $ticket): Response
    {
        $ticket->load(['customer', 'team', 'assignedTo', 'tags']);

        return Inertia::render('TeamTickets/Show', [
            'ticket' => $ticket,
        ]);
    }

    public function edit(Ticket $ticket): Response
    {
        $customers = Customer::latest()->get(['id', 'first_name', 'last_name']);
        $teams = HelpdeskTeam::latest()->get(['id', 'name']);

        return Inertia::render('TeamTickets/Edit', [
            'ticket' => $ticket,
            'customers' => $customers->map(fn($c) => ['id' => $c->id, 'name' => $c->first_name . ' ' . $c->last_name]),
            'teams' => $teams,
        ]);
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'customer_id' => 'required|exists:customers,id',
            'team_id' => 'required|exists:helpdesk_teams,id',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'priority' => 'nullable|string|max:50',
            'stage' => 'nullable|string|max:50',
            'deadline' => 'nullable|date',
        ]);

        $ticket->update($validated);

        return redirect()->route('teamtickets.index')->with('success', 'Ticket updated successfully.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        $ticket->delete();
        return redirect()->route('teamtickets.index')->with('success', 'Ticket deleted successfully.');
    }
}

