<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\HelpdeskTeam;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

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
        
        $query = Ticket::query()
            ->with(['customer', 'team', 'assignedTo'])
            ->visibleTo($user);

        // Get employee and their teams for access checks (separate from filtering)
        $employee = Employee::where('user_id', $user->id)->with('helpdeskTeams')->first();
        if (!$employee) {
            $employee = Employee::where('email', $user->email)->with('helpdeskTeams')->first();
        }
        $employeeId = $employee ? $employee->id : null;
        $userTeamIds = $employee ? $employee->helpdeskTeams->pluck('id')->toArray() : [];

        $team = null;
        if ($request->filled('team')) {
            $teamId = $request->input('team');
            $team = HelpdeskTeam::findOrFail($teamId);
            
            // Check if user can view this team's tickets
            $isUserTeam = in_array($teamId, $userTeamIds);

            if (!$isUserTeam) {
                abort(403, 'Unauthorized. You can only view tickets from your teams.');
            }

            $query->where('team_id', $teamId);
        } else {
            // When no specific team requested, rely on visibility scope to restrict by team if needed
        }

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

        // Assigned filter
        if ($request->filled('assigned')) {
            $assignedFilter = $request->input('assigned');
            if ($assignedFilter === 'me' && $employeeId) {
                $query->where('employee_id', $employeeId);
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

        // Total visible tickets for this user (unfiltered)
        $totalAll = Ticket::query()->visibleTo($user)->count();

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
            'team' => $team,
            'teams' => HelpdeskTeam::all(['id', 'team_name']),
            'totalAll' => $totalAll,
            'filters' => $request->only([
                'search', 'stage', 'priority', 'assigned',
                'date_from', 'date_to', 'deadline_from', 'deadline_to',
                'sort_field', 'sort_direction'
            ])
        ]);
    }

    public function create(): Response
    {
        $customers = Customer::latest()->get(['id', 'first_name', 'last_name']);
        $teams = HelpdeskTeam::orderByDesc('id')->select('id', 'team_name')->get();

        $user = Auth::user();
        $employeeId = null;
        if ($user) {
            $employeeId = Employee::where('user_id', $user->id)->value('id');

            if (! $employeeId && $user->email) {
                $employeeId = Employee::where('email', $user->email)->value('id');
            }
        }

        return Inertia::render('TeamTickets/Create', [
            'customers' => $customers,
            'teams' => $teams,
            'employees' => Employee::all(['id', 'first_name', 'last_name']),
            'tags' => Tag::all(['id', 'name']),
            'priorities' => ['Low', 'Medium', 'High', 'Urgent'],
            'stages' => ['Open', 'In Progress', 'Resolved', 'Closed'],
            'defaultTeamId' => request()->query('team_id'),
            'currentEmployeeId' => $employeeId ? (int)$employeeId : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'customer_id' => 'required|exists:customers,id',
            'team_id' => 'required|exists:helpdesk_teams,id',
            'employee_id' => 'nullable|exists:employees,id',
            'priority' => 'nullable|string|max:50',
            'stage' => 'nullable|string|max:50',
            'deadline' => 'nullable|date',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        Ticket::create($validated);
        // sync tags if provided (created ticket instance would be needed to sync)
        $ticket = Ticket::latest('id')->first();
        if ($ticket) {
            $ticket->tags()->sync($request->input('tag_ids', []));
        }

        return redirect()->route('teamtickets.index')->with('success', 'Ticket created successfully.');
    }

    public function show(Ticket $ticket): Response
    {
        $ticket->load(['customer', 'team', 'assignedTo', 'tags']);

        $perPage = 20;
        $latest = $ticket->messages()->with('user')->orderBy('created_at', 'desc')->take($perPage)->get()->reverse()->values();
        $messagesCount = $ticket->messages()->count();

        return Inertia::render('TeamTickets/Show', [
            'ticket' => $ticket,
            'messages' => $latest,
            'messages_count' => $messagesCount,
        ]);
    }

    public function edit(Ticket $ticket): Response
    {
        $customers = Customer::latest()->get(['id', 'first_name', 'last_name']);
        $teams = HelpdeskTeam::orderByDesc('id')->select('id', 'team_name')->get();

        $employees = Employee::all(['id', 'first_name', 'last_name']);

        $priorities = ['Low', 'Medium', 'High', 'Urgent'];
        $stages = ['Open', 'In Progress', 'Resolved', 'Closed'];

        $ticket->load(['tags']);

        return Inertia::render('TeamTickets/Edit', [
            'ticket' => $ticket,
            'customers' => $customers,
            'teams' => $teams,
            'employees' => $employees,
            'priorities' => $priorities,
            'stages' => $stages,
            'tags' => Tag::all(['id', 'name']),
        ]);
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'customer_id' => 'required|exists:customers,id',
            'team_id' => 'required|exists:helpdesk_teams,id',
            'employee_id' => 'nullable|exists:employees,id',
            'priority' => 'nullable|string|max:50',
            'stage' => 'nullable|string|max:50',
            'deadline' => 'nullable|date',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        $ticket->update($validated);
        $ticket->tags()->sync($request->input('tag_ids', []));

        return redirect()->route('teamtickets.index')->with('success', 'Ticket updated successfully.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        $ticket->delete();
        return redirect()->route('teamtickets.index')->with('success', 'Ticket deleted successfully.');
    }

    /**
     * Bulk update tickets for teams.
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
        if (($updates['employee_id'] ?? null) === 'null') {
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
                || $user->hasPermissionTo('edit_teamtickets');

            if ($canEdit) {
                $ticket->update($updates);
                $count++;
            }
        }

        return redirect()->route('teamtickets.index')
            ->with('message', "Successfully updated {$count} ticket(s).");
    }

    /**
     * Bulk delete tickets for teams.
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
                || $user->hasPermissionTo('delete_teamtickets');

            if ($canDelete) {
                $ticket->delete();
                $count++;
            }
        }

        return redirect()->route('teamtickets.index')
            ->with('message', "Successfully deleted {$count} ticket(s).");
    }
}

