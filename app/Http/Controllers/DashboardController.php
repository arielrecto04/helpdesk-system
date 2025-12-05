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

        // Global stats
        $stats = [
            'totalTickets' => Ticket::count(),
            'openTickets' => Ticket::where('stage', 'Open')->count(),
            'resolvedToday' => Ticket::where('stage', 'Resolved')
                ->whereDate('updated_at', today())
                ->count(),
            'averageResponseTime' => $this->calculateAverageResponseTime()
        ];

        // Additional global breakdowns used in the dashboard header
        $globalStats = [
            'AllhighPriorityTickets' => Ticket::where('priority', 'High')
                ->where('stage', '!=', 'Closed')
                ->where('stage', '!=', 'Resolved')
                ->count(),
            'AllurgentTickets' => Ticket::where('priority', 'Urgent')
                ->where('stage', '!=', 'Closed')
                ->where('stage', '!=', 'Resolved')
                ->count(),
            'AllfailedTickets' => Ticket::whereDate('deadline', '<', now())
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
            'TodaysuccessRate' => $this->calculateUserSuccessRate($employeeId),
            'TodayaverageRating' => $this->calculateUserAverageRating($employeeId),
            'weeklyAvg' => [
                'closed' => $this->calculateWeeklyClosedAverage($employeeId),
                'success' => $this->calculateWeeklySuccessRate($employeeId),
                'rating' => $this->calculateWeeklyAverageRating($employeeId)
            ]
        ];

        $teams = HelpdeskTeam::all()->map(function ($team) use ($user) {
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
                        ->count(),
                    'failed' => Ticket::where('team_id', $team->id)
                        ->whereDate('deadline', '<', now())
                        ->where('stage', '!=', 'Resolved')
                        ->count()
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