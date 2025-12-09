<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\HelpdeskTeam;
use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class AllTicketsController extends Controller
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

        // Apply user visibility filters based on permissions
        $query = Ticket::query()
            ->with(['customer', 'team', 'assignedTo'])
            ->latest()
            ->visibleTo($user);

        $tickets = $query->paginate(15)->withQueryString();

        $tickets->getCollection()->transform(function ($ticket) use ($user) {
            if ($ticket->assignedTo) {
                $ticket->assigned_employee_name = $ticket->assignedTo->first_name . ' ' . $ticket->assignedTo->last_name;
                $ticket->assigned_employee_is_current_user = $user ? ($ticket->assignedTo->user_id === $user->id) : false;
            } else {
                $ticket->assigned_employee_name = null;
                $ticket->assigned_employee_is_current_user = false;
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

        $user = Auth::user();

        // Prefer linked employee by user_id, fall back to email-based detection
        $employeeId = null;
        if ($user) {
            $employeeId = Employee::where('user_id', $user->id)->value('id');

            if (! $employeeId && $user->email) {
                $employeeId = Employee::where('email', $user->email)->value('id');
            }
        }

        return Inertia::render('AllTickets/Create', [
            'customers' => Customer::all(['id', 'first_name', 'last_name']),
            'teams' => HelpdeskTeam::all(),
            'employees' => Employee::all(['id', 'first_name', 'last_name']),
            'priorities' => ['Low', 'Medium', 'High', 'Urgent'],
            'stages' => ['Open', 'In Progress', 'Resolved', 'Closed'],
            'defaultTeamId' => $request->query('team_id'),
            
            'currentEmployeeId' => $employeeId ? (int)$employeeId : null,
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
            'stage' => ['required', Rule::in(['Open', 'In Progress', 'Resolved', 'Closed'])],
            'team_id' => 'required|exists:helpdesk_teams,id',
            'assigned_to_employee_id' => 'nullable|exists:employees,id',
            'deadline' => 'nullable|date',
        ]);
        $ticket = Ticket::create($validated);
        return redirect()->route('alltickets.index')->with('message', 'Ticket created successfully.');
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
        $ticket->load(['customer', 'assignedTo', 'team']);
        $ticket->assigned_employee_name = $ticket->assignedTo ? $ticket->assignedTo->first_name . ' ' . $ticket->assignedTo->last_name : null;

        return Inertia::render('AllTickets/Show', [
            'ticket' => $ticket,
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
        $employeeId = Employee::where('user_id', $user->id)->value('id');
        $hasTeamAccess = $user->teams()->where('helpdesk_teams.id', $ticket->team_id)->exists();

        $canEdit = $hasTeamAccess
            || ($ticket->assigned_to_employee_id !== null && $employeeId !== null && $ticket->assigned_to_employee_id === $employeeId)
            || $user->hasPermissionTo('edit_alltickets');

        if (!$canEdit) {
            abort(403, 'Unauthorized. You can only edit tickets you are allowed to.');
        }

        return Inertia::render('AllTickets/Edit', [
            'ticket' => $ticket,
            'customers' => Customer::all(['id', 'first_name', 'last_name']),
            'teams' => HelpdeskTeam::all(),
            'employees' => Employee::all(['id', 'first_name', 'last_name', 'user_id']),
            'priorities' => ['Low', 'Medium', 'High', 'Urgent'],
            'stages' => ['Open', 'In Progress', 'Resolved', 'Closed'],
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
        $employeeId = Employee::where('user_id', $user->id)->value('id');
        $hasTeamAccess = $user->teams()->where('helpdesk_teams.id', $ticket->team_id)->exists();

        $canEdit = $hasTeamAccess
            || ($ticket->assigned_to_employee_id !== null && $employeeId !== null && $ticket->assigned_to_employee_id === $employeeId)
            || $user->hasPermissionTo('edit_alltickets');

        if (!$canEdit) {
            abort(403, 'Unauthorized. You can only update tickets you are allowed to.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => ['required', Rule::in(['Low', 'Medium', 'High', 'Urgent'])],
            'stage' => ['required', Rule::in(['Open', 'In Progress', 'Resolved', 'Closed'])],
            'team_id' => 'required|exists:helpdesk_teams,id',
            'assigned_to_employee_id' => 'nullable|exists:employees,id',
            'deadline' => 'nullable|date',
        ]);

        $ticket->update($validated);

        return redirect()->route('alltickets.show', $ticket->id)->with('message', 'Ticket updated successfully.');
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
        $employeeId = Employee::where('user_id', $user->id)->value('id');
        $hasTeamAccess = $user->teams()->where('helpdesk_teams.id', $ticket->team_id)->exists();

        $canDelete = $hasTeamAccess
            || ($ticket->assigned_to_employee_id !== null && $employeeId !== null && $ticket->assigned_to_employee_id === $employeeId)
            || $user->hasPermissionTo('delete_alltickets');

        if (!$canDelete) {
            abort(403, 'Unauthorized. You can only delete tickets you are allowed to.');
        }

        $ticket->delete();
        return Redirect::route('alltickets.index')
            ->with('message', 'Ticket deleted successfully.');
    }
}