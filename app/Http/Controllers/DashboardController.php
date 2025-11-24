<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\CustomerRating;
use App\Models\HelpdeskTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Employee;

class DashboardController extends Controller
{
    private function calculateAverageResponseTime()
    {
        // TODO: Implement actual calculation
        // This should calculate the average time between ticket creation and first response
        return '2.5h';
    }

    private function calculateUserSuccessRate($employeeId)
    {
        $totalResolved = Ticket::where('assigned_to_employee_id', $employeeId)
            ->where('stage', 'Resolved')
            ->count();

        $totalAssigned = Ticket::where('assigned_to_employee_id', $employeeId)
            ->count();

        return $totalAssigned > 0 ? round(($totalResolved / $totalAssigned) * 100) : 0;
    }

    private function calculateUserAverageRating($employeeId)
    {
        $ratings = \App\Models\CustomerRating::whereHas('ticket', function ($query) use ($employeeId) {
            $query->where('assigned_to_employee_id', $employeeId);
        })->avg('rating');

        return round($ratings ?? 0, 1);
    }

    private function calculateWeeklyClosedAverage($employeeId)
    {
        $weeklyCount = Ticket::where('assigned_to_employee_id', $employeeId)
            ->where('stage', 'Resolved')
            ->whereBetween('updated_at', [now()->subDays(7), now()])
            ->count();

        return round($weeklyCount / 7, 1);
    }

    private function calculateWeeklySuccessRate($employeeId)
    {
        $totalResolved = Ticket::where('assigned_to_employee_id', $employeeId)
            ->where('stage', 'Resolved')
            ->whereBetween('updated_at', [now()->subDays(7), now()])
            ->count();

        $totalAssigned = Ticket::where('assigned_to_employee_id', $employeeId)
            ->whereBetween('created_at', [now()->subDays(7), now()])
            ->count();

        return $totalAssigned > 0 ? round(($totalResolved / $totalAssigned) * 100) : 0;
    }

    private function calculateWeeklyAverageRating($employeeId)
    {
        $ratings = \App\Models\CustomerRating::whereHas('ticket', function ($query) use ($employeeId) {
            $query->where('assigned_to_employee_id', $employeeId);
        })
        ->whereBetween('submitted_on', [now()->subDays(7), now()])
        ->avg('rating');

        return round($ratings ?? 0, 1);
    }
    public function index()
    {
        $user = Auth::user();
        /** @var User $user */
        $isAdmin = $user->hasRole('admin');

        // Global stats
        $stats = [
            'totalTickets' => Ticket::count(),
            'openTickets' => Ticket::where('stage', 'Open')->count(),
            'resolvedToday' => Ticket::where('stage', 'Resolved')
                ->whereDate('updated_at', today())
                ->count(),
            'averageResponseTime' => $this->calculateAverageResponseTime()
        ];

        // User's personal stats - for admin show all tickets, for others show only their assigned tickets
        $ticketsQuery = Ticket::query();
        $employeeId = Employee::where('user_id', $user->id)->value('id');
        if (!$isAdmin) {
            if ($employeeId) {
                $ticketsQuery->where('assigned_to_employee_id', $employeeId);
            } else {
                // No employee record -> no assigned tickets
                $ticketsQuery->whereNull('id');
            }
        }

        $userStats = [
            'highPriorityTickets' => (clone $ticketsQuery)
                ->where('priority', 'High')
                ->where('stage', '!=', 'Closed')
                ->count(),
            'urgentTickets' => (clone $ticketsQuery)
                ->where('priority', 'Urgent')
                ->where('stage', '!=', 'Closed')
                ->count(),
            'failedTickets' => (clone $ticketsQuery)
                ->whereDate('deadline', '<', now())
                ->where('stage', '!=', 'Resolved')
                ->where('stage', '!=', 'Closed')
                ->count(),
            'closedTickets' => ($employeeId ? Ticket::where('assigned_to_employee_id', $employeeId) : Ticket::whereNull('id'))
                ->where('stage', 'Resolved')
                ->whereDate('updated_at', today())
                ->count(),
            'successRate' => $this->calculateUserSuccessRate($employeeId),
            'averageRating' => $this->calculateUserAverageRating($employeeId),
            'weeklyAvg' => [
                'closed' => $this->calculateWeeklyClosedAverage($employeeId),
                'success' => $this->calculateWeeklySuccessRate($employeeId),
                'rating' => $this->calculateWeeklyAverageRating($employeeId)
            ]
        ];

        // Team statistics
        $teams = HelpdeskTeam::all()->map(function ($team) use ($user, $isAdmin) {
            $totalTickets = Ticket::where('team_id', $team->id)->count();
            $resolvedTickets = Ticket::where('team_id', $team->id)
                ->where('stage', 'Resolved')
                ->count();
            
            return [
                'id' => $team->id,
                'name' => $team->team_name,
                'stats' => [
                    'closed' => $resolvedTickets,
                    'success' => $totalTickets > 0 
                        ? round(($resolvedTickets / $totalTickets) * 100) 
                        : 0,
                    'rating' => round(CustomerRating::whereHas('ticket', function ($query) use ($team) {
                        $query->where('team_id', $team->id);
                    })->avg('rating') ?? 0, 1)
                ],
                'queue' => [
                    'open' => Ticket::where('team_id', $team->id)
                        ->where('stage', 'Open')
                        ->count(),
                    'unassigned' => Ticket::where('team_id', $team->id)
                        ->whereNull('assigned_to_employee_id')
                        ->count(),
                    'urgent' => Ticket::where('team_id', $team->id)
                        ->where('priority', 'Urgent')
                        // ->where('stage', '!=', 'Closed')
                        // ->where('stage', '!=', 'Resolved')
                        ->count(),
                    'failed' => Ticket::where('team_id', $team->id)
                        ->whereDate('deadline', '<', now())
                        ->where('stage', '!=', 'Resolved')
                        // ->where('stage', '!=', 'Closed')
                        ->count()
                ]
            ];
        });

        // Compute per-team access flags for the current user
        $teams = $teams->map(function ($team) use ($user, $isAdmin) {
            $hasTeamAccess = $isAdmin || $user->teams()->where('helpdesk_teams.id', $team['id'])->exists();
            $hasPermissionAccess = $user->hasPermissionTo('view_team_tickets')
                || $user->hasPermissionTo('view_all_tickets')
                || $user->hasPermissionTo('view_tickets')
                || $user->hasPermissionTo('can_view_other_teams_tickets')
                || $user->hasPermissionTo('can_view_other_locations_tickets')
                || $user->hasPermissionTo('can_view_other_users_tickets');

            $team['canView'] = $hasTeamAccess || $hasPermissionAccess;
            return $team;
        });

        // Recent activities
        $recentActivities = Ticket::with(['customer', 'assignedTo', 'team'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'priority' => $ticket->priority,
                    'subject' => $ticket->subject,
                    'stage' => $ticket->stage,
                    'time' => $ticket->created_at->diffForHumans(),
                    'customer' => $ticket->customer ? "{$ticket->customer->first_name} {$ticket->customer->last_name}" : 'N/A',
                    'assignedTo' => $ticket->assignedTo ? "{$ticket->assignedTo->first_name} {$ticket->assignedTo->last_name}" : 'Unassigned',
                    'team' => $ticket->team ? $ticket->team->team_name : 'Unassigned'
                ];
            });

        // Prepare auth data with roles and permissions
        $permissions = $user->roles()->with('permissions')->get()
            ->flatMap(function ($role) { return $role->permissions->pluck('name'); })
            ->unique()
            ->values()
            ->toArray();

        $authData = [
            'user' => array_merge($user->toArray(), [
                'roles' => $user->roles()->pluck('name')->toArray(),
                'permissions' => $permissions,
                'employee_id' => $employeeId,
            ])
        ];

        // Return the dashboard view with all required data
        return Inertia::render('Dashboard', [
            'auth' => $authData,
            'stats' => $stats,
            'userStats' => $userStats,
            'teams' => $teams,
            'recentActivities' => $recentActivities,
            'isAdmin' => $isAdmin
        ]);
    }
}