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
            ->latest()
            ->visibleTo($user);

        // Get employee and their teams for access checks (separate from filtering)
        $employee = Employee::where('user_id', $user->id)->with('helpdeskTeams')->first();
        if (!$employee) {
            $employee = Employee::where('email', $user->email)->with('helpdeskTeams')->first();
        }
        $userTeamIds = $employee ? $employee->helpdeskTeams->pluck('id')->toArray() : [];

        $team = null;
        if ($request->filled('team')) {
            $teamId = $request->input('team');
            $team = HelpdeskTeam::findOrFail($teamId);
            
            // Check if user can view this team's tickets
            $isUserTeam = in_array($teamId, $userTeamIds);
            
            // can_view_other_teams_tickets - pwede makita ang tickets ng nasa ibang helpdesk teams
            $canViewOtherTeams = $user->hasPermissionTo('can_view_other_teams_tickets');

            if (!$isUserTeam && !$canViewOtherTeams) {
                abort(403, 'Unauthorized. You can only view tickets from your teams.');
            }

            $query->where('team_id', $teamId);
        } else {
            // When no specific team requested, rely on visibility scope to restrict by team if needed
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
            'filters' => $request->only(['search', 'status', 'priority', 'team']),
            'team' => $team,
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
            'assigned_to_employee_id' => 'nullable|exists:employees,id',
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
            'assigned_to_employee_id' => 'nullable|exists:employees,id',
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
}

