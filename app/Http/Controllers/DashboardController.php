<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\CustomerRating;
use App\Models\HelpdeskTeam;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Employee;

class DashboardController extends Controller
{
    private function calculateAverageResponseTime()
    {
        // TODO: Implement actual calculation based on ticket response times
        return '2.5h';
    }

    private function calculateUserSuccessRate($employeeId)
    {
        if (empty($employeeId)) {
            return 0;
        }
        $totalResolved = Ticket::where('assigned_to_employee_id', $employeeId)
            ->where('stage', 'Resolved')
            ->count();

        $totalAssigned = Ticket::where('assigned_to_employee_id', $employeeId)
            ->count();

        return $totalAssigned > 0 ? round(($totalResolved / $totalAssigned) * 100) : 0;
    }

    private function calculateUserAverageRating($employeeId)
    {
        if (empty($employeeId)) {
            return 0;
        }
        $ratings = \App\Models\CustomerRating::whereHas('ticket', function ($query) use ($employeeId) {
            $query->where('assigned_to_employee_id', $employeeId);
        })->avg('rating');

        return round($ratings ?? 0, 1);
    }

    private function calculateWeeklyClosedAverage($employeeId)
    {
        if (empty($employeeId)) {
            return 0;
        }
        $weeklyCount = Ticket::where('assigned_to_employee_id', $employeeId)
            ->where('stage', 'Resolved')
            ->whereBetween('updated_at', [now()->subDays(7), now()])
            ->count();

        return round($weeklyCount / 7, 1);
    }

    private function calculateWeeklySuccessRate($employeeId)
    {
        if (empty($employeeId)) {
            return 0;
        }
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
        if (empty($employeeId)) {
            return 0;
        }
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

        // Base query respecting user visibility permissions
        $visibleTickets = Ticket::query()->visibleTo($user);

        // Global stats (respecting permissions)
        $stats = [
            'totalTickets' => (clone $visibleTickets)->count(),
            'openTickets' => (clone $visibleTickets)->where('stage', 'Open')->count(),
            'resolvedToday' => (clone $visibleTickets)->where('stage', 'Resolved')
                ->whereDate('updated_at', today())
                ->count(),
            'averageResponseTime' => $this->calculateAverageResponseTime()
        ];

        // Additional global breakdowns used in the dashboard header
        $globalStats = [
            'AllhighPriorityTickets' => (clone $visibleTickets)
                ->where('priority', 'High')
                ->where('stage', '!=', 'Closed')
                ->where('stage', '!=', 'Resolved')
                ->count(),
            'AllurgentTickets' => (clone $visibleTickets)
                ->where('priority', 'Urgent')
                ->where('stage', '!=', 'Closed')
                ->where('stage', '!=', 'Resolved')
                ->count(),
            'AllfailedTickets' => (clone $visibleTickets)
                ->whereDate('deadline', '<', now())
                ->where('stage', '!=', 'Resolved')
                ->where('stage', '!=', 'Closed')
                ->where('closed_at', null)
                ->count()
        ];

        // Load employee record and their teams
        $employee = Employee::where('user_id', $user->id)
            ->with('helpdeskTeams')
            ->first();
        
        if (!$employee) {
            $employee = Employee::where('email', $user->email)
                ->with('helpdeskTeams')
                ->first();
        }
        
        $employeeId = $employee?->id;
        $ticketsQuery = Ticket::query();

        if ($employeeId) {
            $ticketsQuery->where('assigned_to_employee_id', $employeeId);
        } else {
            $ticketsQuery->whereNull('id');
        }

        $userStats = [
            // Total tickets assigned to this user
            'AllAssignedTickets' => (clone $ticketsQuery)->count(),
            'AllhighPriorityTickets' => (clone $ticketsQuery)
                ->where('priority', 'High')
                ->where('stage', '!=', 'Closed')
                ->where('stage', '!=', 'Resolved')
                ->count(),
            'AllurgentTickets' => (clone $ticketsQuery)
                ->where('priority', 'Urgent')
                ->where('stage', '!=', 'Closed')
                ->where('stage', '!=', 'Resolved')
                ->count(),
            'AllfailedTickets' => (clone $ticketsQuery)
                ->whereDate('deadline', '<', now())
                ->where('stage', '!=', 'Resolved')
                ->where('stage', '!=', 'Closed')
                ->where('closed_at', null)
                ->count(),

            'AllClosedTickets' => (clone $ticketsQuery)
                ->where('stage', 'Resolved')
                ->where('closed_at', '!=', null)
                ->count(),


            'TodayclosedTickets' => (clone $ticketsQuery)
                ->where('stage', 'Resolved')
                ->whereDate('updated_at', today())
                ->count(),
            'TodayAssignedTickets' => (clone $ticketsQuery)
                ->whereDate('created_at', today())
                ->count(),
            'TodayDeadlineTickets' => (clone $ticketsQuery)
                ->whereDate('deadline', today())
                ->count(),
            'TodaysuccessRate' => $this->calculateUserSuccessRate($employeeId),
            'TodayaverageRating' => $this->calculateUserAverageRating($employeeId),
            'weeklyAvg' => [
                'closed' => $this->calculateWeeklyClosedAverage($employeeId),
                'success' => $this->calculateWeeklySuccessRate($employeeId),
                'rating' => $this->calculateWeeklyAverageRating($employeeId)
            ]
        ];

        $teams = HelpdeskTeam::all()->map(function ($team) use ($user, $employeeId, $visibleTickets) {
            $teamVisible = (clone $visibleTickets)->where('team_id', $team->id);

            $totalTickets = (clone $teamVisible)->count();
            $resolvedTickets = (clone $teamVisible)
                ->where('stage', 'Resolved')
                ->count();

            // User-specific stats for this team (tickets assigned to the current employee)
            $userClosed = $employeeId ? Ticket::where('team_id', $team->id)
                ->where('assigned_to_employee_id', $employeeId)
                ->where('stage', 'Resolved')
                ->count() : 0;

            $userTotal = $employeeId ? Ticket::where('team_id', $team->id)
                ->where('assigned_to_employee_id', $employeeId)
                ->count() : 0;

            $userSuccess = $userTotal > 0 ? round(($userClosed / $userTotal) * 100) : 0;

            $userRating = $employeeId ? round(CustomerRating::whereHas('ticket', function ($query) use ($team, $employeeId) {
                $query->where('team_id', $team->id)
                      ->where('assigned_to_employee_id', $employeeId);
            })->avg('rating') ?? 0, 1) : 0;

            // Queue counts for the user within this team
            $userOpen = $employeeId ? Ticket::where('team_id', $team->id)
                ->where('assigned_to_employee_id', $employeeId)
                ->where('stage', 'Open')
                ->count() : 0;

            $userUrgent = $employeeId ? Ticket::where('team_id', $team->id)
                ->where('assigned_to_employee_id', $employeeId)
                ->where('priority', 'Urgent')
                ->count() : 0;

            $userFailed = $employeeId ? Ticket::where('team_id', $team->id)
                ->where('assigned_to_employee_id', $employeeId)
                ->whereDate('deadline', '<', now())
                ->where('stage', '!=', 'Resolved')
                ->count() : 0;

            return [
                'id' => $team->id,
                'name' => $team->team_name,
                'ticketCount' => $totalTickets,
                'stats' => [
                    'closed' => $resolvedTickets,
                    'success' => $totalTickets > 0 
                        ? round(($resolvedTickets / $totalTickets) * 100) 
                        : 0,
                    'rating' => round(CustomerRating::whereHas('ticket', function ($query) use ($team) {
                        $query->where('team_id', $team->id);
                    })->avg('rating') ?? 0, 1)
                ],
                // Add user-specific stats so frontend can show per-user counts when needed
                'userStats' => [
                    'closed' => $userClosed,
                    'success' => $userSuccess,
                    'rating' => $userRating,
                ],
                'queue' => [
                    'open' => (clone $teamVisible)
                        ->where('stage', 'Open')
                        ->count(),
                    'unassigned' => (clone $teamVisible)
                        ->whereNull('assigned_to_employee_id')
                        ->count(),
                    'urgent' => (clone $teamVisible)
                        ->where('priority', 'Urgent')
                        ->count(),
                    'failed' => (clone $teamVisible)
                        ->whereDate('deadline', '<', now())
                        ->where('stage', '!=', 'Resolved')
                        ->count(),
                    // user-specific queue breakdown
                    'user' => [
                        'open' => $userOpen,
                        // 'unassigned' for a specific user usually not applicable; set to 0
                        'unassigned' => 0,
                        'urgent' => $userUrgent,
                        'failed' => $userFailed,
                    ]
                ]
            ];
        });

        // Determine team access for current user
        $employeeTeamIds = $employee ? $employee->helpdeskTeams->pluck('id')->toArray() : [];

        $teams = $teams->map(function ($team) use ($user, $employeeTeamIds) {
            // User can view team if they are a member OR have 'can_view_other_teams_tickets' permission
            $isMember = in_array($team['id'], $employeeTeamIds);
            $canViewOtherTeams = $user->hasPermissionTo('can_view_other_teams_tickets');

            $team['canView'] = $isMember || $canViewOtherTeams;

            return $team;
        });

        // Recent activities
        $recentActivities = (clone $visibleTickets)
            ->with(['customer', 'assignedTo', 'team'])
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

        // Prepare auth data with roles, permissions, and teams
        $permissions = $user->roles()
            ->with('permissions')
            ->get()
            ->flatMap(fn($role) => $role->permissions->pluck('name'))
            ->unique()
            ->values()
            ->toArray();

        $authData = [
            'user' => array_merge($user->toArray(), [
                'roles' => $user->roles()->pluck('name')->toArray(),
                'permissions' => $permissions,
                'employee_id' => $employeeId,
                'teams' => $employee ? $employee->helpdeskTeams->map(fn($t) => [
                    'id' => $t->id,
                    'team_name' => $t->team_name
                ])->toArray() : [],
            ])
        ];

        return Inertia::render('Dashboard', [
            'auth' => $authData,
            'stats' => $stats,
            'userStats' => $userStats,
            'globalStats' => $globalStats,
            'teams' => $teams,
            'recentActivities' => $recentActivities,
        ]);
    }
}