<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\HelpdeskTeam;
use App\Models\Customer;
use App\Models\Employee;
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

        $tickets->getCollection()->transform(function ($ticket) use ($user) {
            if ($ticket->assignedTo) {
                $ticket->assigned_employee_name = $ticket->assignedTo->first_name . ' ' . $ticket->assignedTo->last_name;
                $ticket->assigned_employee_is_current_user = $ticket->assignedTo->user_id === $user->id;
            } else {
                $ticket->assigned_employee_name = null;
                $ticket->assigned_employee_is_current_user = false;
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
        $teams = HelpdeskTeam::orderByDesc('id')->select('id', 'team_name')->get();

        return Inertia::render('TeamTickets/Create', [
            'customers' => $customers,
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
            'assigned_to_employee_id' => 'nullable|exists:employees,id',
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
        $teams = HelpdeskTeam::orderByDesc('id')->select('id', 'team_name')->get();

        return Inertia::render('TeamTickets/Edit', [
            'ticket' => $ticket,
            'customers' => $customers,
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
            'assigned_to_employee_id' => 'nullable|exists:employees,id',
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

