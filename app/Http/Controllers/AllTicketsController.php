<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\HelpdeskTeam;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Tag;
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
    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Apply user visibility filters based on permissions
        $query = Ticket::query()
            ->with(['customer', 'team', 'assignedTo'])
            ->visibleTo($user);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('assignedTo', function ($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('email', 'like', "%{$search}%");
                        });
                  });
            });
        }

        // Stage filter
        if ($request->filled('stage')) {
            $query->where('stage', $request->input('stage'));
        }

        // Priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        // Team filter (expects team id from dropdown)
        if ($request->filled('team')) {
            $query->where('team_id', $request->input('team'));
        }

        // Assigned filter
        if ($request->filled('assigned')) {
            $assignedFilter = $request->input('assigned');
            if ($assignedFilter === 'me') {
                $query->whereHas('assignedTo', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            } elseif ($assignedFilter === 'unassigned') {
                $query->whereNull('employee_id');
            }
        }

        // Created date filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Deadline date filter
        if ($request->filled('deadline_from')) {
            $query->whereDate('deadline', '>=', $request->input('deadline_from'));
        }
        if ($request->filled('deadline_to')) {
            $query->whereDate('deadline', '<=', $request->input('deadline_to'));
        }

        // Sorting
        if ($request->filled('sort_field')) {
            $sortField = $request->input('sort_field');
            $sortDirection = $request->input('sort_direction', 'asc');
            
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->latest();
        }

        $tickets = $query->paginate(15)->withQueryString();

        // Total visible tickets (unfiltered) for summary display
        $totalAll = Ticket::query()->visibleTo($user)->count();

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
            'pageTitle' => 'All Tickets',
            'teams' => HelpdeskTeam::all(['id', 'team_name']),
            'totalAll' => $totalAll,
            'filters' => $request->only([
                'search', 'stage', 'priority', 'team', 'assigned',
                'date_from', 'date_to', 'deadline_from', 'deadline_to',
                'sort_field', 'sort_direction'
            ])
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
            'tags' => Tag::all(['id', 'name']),
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
            'employee_id' => 'nullable|exists:employees,id',
            'deadline' => 'nullable|date',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);
        $ticket = Ticket::create($validated);
        // Attach tags if provided
        $ticket->tags()->sync($request->input('tag_ids', []));
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
        $ticket->load(['customer', 'assignedTo', 'team', 'tags']);
        $ticket->assigned_employee_name = $ticket->assignedTo ? $ticket->assignedTo->first_name . ' ' . $ticket->assignedTo->last_name : null;

        // Provide recent messages (latest N) for initial render and total count for pagination
        $perPage = 20;
        $latest = $ticket->messages()->with('user')->orderBy('created_at', 'desc')->take($perPage)->get()->reverse()->values();
        $messagesCount = $ticket->messages()->count();

        return Inertia::render('AllTickets/Show', [
            'ticket' => $ticket,
            'messages' => $latest,
            'messages_count' => $messagesCount,
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
            || ($ticket->employee_id !== null && $employeeId !== null && $ticket->employee_id === $employeeId)
            || $user->hasPermissionTo('edit_alltickets');

        if (!$canEdit) {
            abort(403, 'Unauthorized. You can only edit tickets you are allowed to.');
        }

        $ticket->load(['tags']);

        return Inertia::render('AllTickets/Edit', [
            'ticket' => $ticket,
            'customers' => Customer::all(['id', 'first_name', 'last_name']),
            'teams' => HelpdeskTeam::all(),
            'employees' => Employee::all(['id', 'first_name', 'last_name', 'user_id']),
            'priorities' => ['Low', 'Medium', 'High', 'Urgent'],
            'stages' => ['Open', 'In Progress', 'Resolved', 'Closed'],
            'tags' => Tag::all(['id', 'name']),
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
            || ($ticket->employee_id !== null && $employeeId !== null && $ticket->employee_id === $employeeId)
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
            'employee_id' => 'nullable|exists:employees,id',
            'deadline' => 'nullable|date',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        $ticket->update($validated);
        $ticket->tags()->sync($request->input('tag_ids', []));

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
            || ($ticket->employee_id !== null && $employeeId !== null && $ticket->employee_id === $employeeId)
            || $user->hasPermissionTo('delete_alltickets');

        if (!$canDelete) {
            abort(403, 'Unauthorized. You can only delete tickets you are allowed to.');
        }

        $ticket->delete();
        return Redirect::route('alltickets.index')
            ->with('message', 'Ticket deleted successfully.');
    }

    /**
     * Bulk update tickets.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkUpdate(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        
        $validated = $request->validate([
            'ticket_ids' => 'required|array',
            'ticket_ids.*' => 'exists:tickets,id',
            'updates' => 'required|array',
            'updates.stage' => 'nullable|in:Open,In Progress,Resolved,Closed',
            'updates.priority' => 'nullable|in:Low,Medium,High,Urgent',
            'updates.employee_id' => 'nullable|exists:employees,id',
        ]);

        $ticketIds = $validated['ticket_ids'];
        $updates = array_filter($validated['updates'], fn($val) => $val !== null && $val !== '');
        
        if ($updates['employee_id'] ?? null === 'null') {
            $updates['employee_id'] = null;
        }

        $count = 0;
        foreach ($ticketIds as $ticketId) {
            $ticket = Ticket::find($ticketId);
            if (!$ticket) continue;

            $employeeId = Employee::where('user_id', $user->id)->value('id');
            $hasTeamAccess = $user->teams()->where('helpdesk_teams.id', $ticket->team_id)->exists();

            $canEdit = $hasTeamAccess
                || ($ticket->employee_id !== null && $employeeId !== null && $ticket->employee_id === $employeeId)
                || $user->hasPermissionTo('edit_alltickets');

            if ($canEdit) {
                $ticket->update($updates);
                $count++;
            }
        }

        return Redirect::route('alltickets.index')
            ->with('message', "Successfully updated {$count} ticket(s).");
    }

    /**
     * Bulk delete tickets.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        
        $validated = $request->validate([
            'ticket_ids' => 'required|array',
            'ticket_ids.*' => 'exists:tickets,id',
        ]);

        $ticketIds = $validated['ticket_ids'];
        $count = 0;

        foreach ($ticketIds as $ticketId) {
            $ticket = Ticket::find($ticketId);
            if (!$ticket) continue;

            $employeeId = Employee::where('user_id', $user->id)->value('id');
            $hasTeamAccess = $user->teams()->where('helpdesk_teams.id', $ticket->team_id)->exists();

            $canDelete = $hasTeamAccess
                || ($ticket->employee_id !== null && $employeeId !== null && $ticket->employee_id === $employeeId)
                || $user->hasPermissionTo('delete_alltickets');

            if ($canDelete) {
                $ticket->delete();
                $count++;
            }
        }

        return Redirect::route('alltickets.index')
            ->with('message', "Successfully deleted {$count} ticket(s).");
    }
}