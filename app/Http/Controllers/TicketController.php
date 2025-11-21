<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\HelpdeskTeam;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    /**
     * Display a listing of all tickets.
     *
     * @return Response
     */
    public function index(): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = Ticket::query()
            ->with(['customer', 'team', 'assignedTo'])
            ->latest();

        $hasBroadView = $user->hasRole('admin')
            || $user->hasPermissionTo('view_all_tickets')
            || $user->hasPermissionTo('can_view_other_teams_tickets')
            || $user->hasPermissionTo('can_view_other_locations_tickets')
            || $user->hasPermissionTo('can_view_other_users_tickets');

        if (!$hasBroadView) {
            $teamIds = $user->teams()->pluck('helpdesk_teams.id')->toArray();
            $query->where(function ($q) use ($teamIds, $user) {
                $q->whereIn('team_id', $teamIds)
                    ->orWhere('assigned_to_user_id', $user->id);
            });
        }

        $tickets = $query->paginate(15)->withQueryString();

        $tickets->getCollection()->transform(function ($ticket) {
            if ($ticket->assignedTo) {
                $ticket->assigned_user_name = $ticket->assignedTo->first_name . ' ' . $ticket->assignedTo->last_name;
            } else {
                $ticket->assigned_user_name = null;
            }
            return $ticket;
        });
        
        return Inertia::render('AllTickets', [
            'tickets' => $tickets,
            'pageTitle' => 'All Tickets'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Tickets/Create', [
            'customers' => Customer::all(['id', 'first_name', 'last_name']),
            'teams' => HelpdeskTeam::all(),
            'users' => User::all(['id', 'first_name', 'last_name']),
            'priorities' => ['Low', 'Medium', 'High', 'Urgent'],
            'stages' => ['Open', 'In Progress', 'Pending Customer', 'Resolved', 'Closed'],
            'defaultTeamId' => $request->query('team_id'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => ['required', Rule::in(['Low', 'Medium', 'High', 'Urgent'])],
            'stage' => ['required', Rule::in(['Open', 'In Progress', 'Pending Customer', 'Resolved', 'Closed'])],
            'team_id' => 'required|exists:helpdesk_teams,id',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'deadline' => 'nullable|date',
        ]);

        $ticket = Ticket::create($validated);
        // If the request asked to stay on the customer dashboard, redirect there instead
        if ($request->query('redirect') === 'dashboard') {
            return redirect()->route('customer.dashboard')->with('message', 'Ticket created successfully.');
        }

        return redirect()->route('tickets.show', $ticket->id)->with('message', 'Ticket created successfully.');
    }

    /**
     * Display the specified ticket.
     *
     * @param Ticket $ticket
     * @return Response
     */
    public function show(Ticket $ticket): Response
    {
        /** @var User $user */
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');
        $hasTeamAccess = $user->teams()
            ->where('helpdesk_teams.id', $ticket->team_id)
            ->exists();

        // Allow customers who created the ticket to view it
        $isCustomerOwner = false;
        if ($user->customer) {
            $isCustomerOwner = $ticket->customer_id === $user->customer->id;
        }

        // Authorization rules:
        // - Admins can view any ticket
        // - Users who belong to the ticket's team can view
        // - The user assigned to the ticket can view it
        // - Users with explicit permissions can view all tickets
        $canView = $isAdmin
            || $hasTeamAccess
            || ($ticket->assigned_to_user_id !== null && $ticket->assigned_to_user_id === $user->id)
            || $user->hasPermissionTo('view_all_tickets')
            || $user->hasPermissionTo('view_tickets')
            // Allow users with the 'show_my_tickets' permission to view tickets assigned to them
            || ($user->hasPermissionTo('show_my_tickets') && $ticket->assigned_to_user_id !== null && $ticket->assigned_to_user_id === $user->id)
            // New permissions to allow viewing tickets beyond own team/user
            || $user->hasPermissionTo('can_view_other_teams_tickets')
            || $user->hasPermissionTo('can_view_other_locations_tickets')
            || $user->hasPermissionTo('can_view_other_users_tickets');

        // Also allow the customer who created the ticket to view it
        if ($isCustomerOwner) {
            $canView = true;
        }

        if (!$canView) {
            abort(403, 'Unauthorized. You do not have permission to view this ticket.');
        }

        // Load relationships and add assigned_user_name attribute
        $ticket->load(['customer', 'assignedTo', 'team']);
        $ticket->assigned_user_name = $ticket->assignedTo ? $ticket->assignedTo->first_name . ' ' . $ticket->assignedTo->last_name : null;

        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket,
            'isAdmin' => $isAdmin,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Inertia\Response
     */
    public function edit(Ticket $ticket): Response
    {
        /** @var User $user */
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');
        $hasTeamAccess = $user->teams()->where('helpdesk_teams.id', $ticket->team_id)->exists();

        // Allow editing if admin, team member, assigned user, or has edit permissions
        $canEdit = $isAdmin
            || $hasTeamAccess
            || ($ticket->assigned_to_user_id !== null && $ticket->assigned_to_user_id === $user->id)
            || $user->hasPermissionTo('edit_tickets')
            || $user->hasPermissionTo('view_all_tickets')
            || ($user->hasPermissionTo('edit_my_tickets') && $ticket->assigned_to_user_id !== null && $ticket->assigned_to_user_id === $user->id);

        if (!$canEdit) {
            abort(403, 'Unauthorized. You can only edit tickets you are allowed to.');
        }

        return Inertia::render('Tickets/Edit', [
            'ticket' => $ticket,
            'customers' => Customer::all(['id', 'first_name', 'last_name']),
            'teams' => HelpdeskTeam::all(),
            'users' => User::all(['id', 'first_name', 'last_name']),
            'priorities' => ['Low', 'Medium', 'High', 'Urgent'],
            'stages' => ['Open', 'In Progress', 'Pending Customer', 'Resolved', 'Closed'],
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');
        $hasTeamAccess = $user->teams()->where('helpdesk_teams.id', $ticket->team_id)->exists();

        $canEdit = $isAdmin
            || $hasTeamAccess
            || ($ticket->assigned_to_user_id !== null && $ticket->assigned_to_user_id === $user->id)
            || $user->hasPermissionTo('edit_tickets')
            || $user->hasPermissionTo('view_all_tickets');

        if (!$canEdit) {
            abort(403, 'Unauthorized. You can only update tickets you are allowed to.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => ['required', Rule::in(['Low', 'Medium', 'High', 'Urgent'])],
            'stage' => ['required', Rule::in(['Open', 'In Progress', 'Pending Customer', 'Resolved', 'Closed'])],
            'team_id' => 'required|exists:helpdesk_teams,id',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'deadline' => 'nullable|date',
        ]);

        $ticket->update($validated);

        return redirect()->route('tickets.show', $ticket->id)->with('message', 'Ticket updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Ticket $ticket): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');
        $hasTeamAccess = $user->teams()->where('helpdesk_teams.id', $ticket->team_id)->exists();

        // Allow delete if admin, team member, assigned user, or has delete permissions
        $canDelete = $isAdmin
            || $hasTeamAccess
            || ($ticket->assigned_to_user_id !== null && $ticket->assigned_to_user_id === $user->id)
            || $user->hasPermissionTo('delete_tickets')
            || $user->hasPermissionTo('view_all_tickets')
            // Allow users with 'delete_my_tickets' to delete tickets assigned to them
            || ($user->hasPermissionTo('delete_my_tickets') && $ticket->assigned_to_user_id !== null && $ticket->assigned_to_user_id === $user->id);

        if (!$canDelete) {
            abort(403, 'Unauthorized. You can only delete tickets you are allowed to.');
        }

        $teamId = $ticket->team_id;
        $ticket->delete();
        return Redirect::route('team.tickets', ['teamId' => $teamId])
            ->with('message', 'Ticket deleted successfully.');
    }
}