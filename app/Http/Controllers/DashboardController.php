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
        $totalResolved = Ticket::where('employee_id', $employeeId)
            ->where('stage', 'Resolved')
            ->count();

        $totalAssigned = Ticket::where('employee_id', $employeeId)
            ->count();

        return $totalAssigned > 0 ? round(($totalResolved / $totalAssigned) * 100) : 0;
    }

    private function calculateUserAverageRating($employeeId)
    {
        if (empty($employeeId)) {
            return 0;
        }
        $ratings = \App\Models\CustomerRating::whereHas('ticket', function ($query) use ($employeeId) {
            $query->where('employee_id', $employeeId);
        })->avg('rating');

        return round($ratings ?? 0, 1);
    }

    private function calculateWeeklyClosedAverage($employeeId)
    {
        if (empty($employeeId)) {
            return 0;
        }
        $weeklyCount = Ticket::where('employee_id', $employeeId)
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
        $totalResolved = Ticket::where('employee_id', $employeeId)
            ->where('stage', 'Resolved')
            ->whereBetween('updated_at', [now()->subDays(7), now()])
            ->count();

        $totalAssigned = Ticket::where('employee_id', $employeeId)
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
            $query->where('employee_id', $employeeId);
        })
        ->whereBetween('submitted_on', [now()->subDays(7), now()])
        ->avg('rating');

        return round($ratings ?? 0, 1);
    }

    public function index()
    {
        // 1. Authentication
        // Kunin kung sino ang naka-login na user
        $user = Auth::user();
        /** @var User $user */

        // 2. Base Query (Permission Check)
        // Gumawa ng query para sa tickets, pero i-filter agad gamit ang 'visibleTo'.
        // Sinisiguro nito na kung ano lang ang allowed makita ng user (e.g., admin vs normal user), yun lang ang bibilangin.
        $visibleTickets = Ticket::query()->visibleTo($user);

        // 3. Global Stats (General Overview)
        // Ito ang mga numero para sa buong dashboard.
        // Gumagamit tayo ng (clone $visibleTickets) para hindi masira ang main query.
        $stats = [
            'totalTickets' => (clone $visibleTickets)->count(), // Bilang ng lahat
            'openTickets' => (clone $visibleTickets)->where('stage', 'Open')->count(), // Bilang ng Open pa
            'resolvedToday' => (clone $visibleTickets)->where('stage', 'Resolved')
                ->whereDate('updated_at', today()) // Na-resolve ngayong araw
                ->count(),
            'averageResponseTime' => $this->calculateAverageResponseTime() // Custom function computation
        ];

        // 4. Global Breakdowns (Header Stats)
        // Mas detalyadong stats para sa taas ng dashboard (High Priority, Urgent, Failed).
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
                ->whereDate('deadline', '<', now()) // Kung ang deadline ay mas mababa sa "ngayon" (past due)
                ->where('stage', '!=', 'Resolved')
                ->where('stage', '!=', 'Closed')
                ->where('closed_at', null)
                ->count()
        ];

        // 5. Employee Identification
        // Hanapin ang 'Employee' record na nakadugtong sa User account.
        // Mahalaga ito dahil ang tickets ay naka-assign sa Employee ID, hindi sa User ID.
        $employee = Employee::where('user_id', $user->id)
            ->with('helpdeskTeams')
            ->first();
        
        // Fallback: Kung walang user_id match, subukan hanapin gamit ang email.
        if (!$employee) {
            $employee = Employee::where('email', $user->email)
                ->with('helpdeskTeams')
                ->first();
        }
        
        $employeeId = $employee?->id; // Gamitin ang ID kung may nahanap, or null.
        
        // 6. User Specific Query
        // Gumawa ng query na naka-focus lang sa tickets ng empleyado na ito.
        $ticketsQuery = Ticket::query();

        if ($employeeId) {
            $ticketsQuery->where('employee_id', $employeeId); // Filter: Tickets KO lang
        } else {
            $ticketsQuery->whereNull('id'); // Kung hindi employee, walang ibabalik (force empty).
        }

        // 7. User Stats Compilation
        // Ito ang stats ng mismong performance ng user (My Assigned, My Success Rate, etc.)
        $userStats = [
            // Total na naka-assign sa user
            'AllAssignedTickets' => (clone $ticketsQuery)->count(),
            
            // Mga urgent/high priority na naka-assign sa user
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
            // Tickets ng user na nag-fail sa deadline
            'AllfailedTickets' => (clone $ticketsQuery)
                ->whereDate('deadline', '<', now())
                ->where('stage', '!=', 'Resolved')
                ->where('stage', '!=', 'Closed')
                ->where('closed_at', null)
                ->count(),

            // History ng user
            // 'AllClosedTickets' => (clone $ticketsQuery)
            //     ->where('stage', 'Resolved')
            //     ->where('closed_at', '!=', null)
            //     ->count(),

            // Daily Performance ng user
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
            
            // Complex Calculations (NASA ibang functions ito sa baba ng controller mo)
            'TodaysuccessRate' => $this->calculateUserSuccessRate($employeeId),
            'TodayaverageRating' => $this->calculateUserAverageRating($employeeId),
            'weeklyAvg' => [
                'closed' => $this->calculateWeeklyClosedAverage($employeeId),
                'success' => $this->calculateWeeklySuccessRate($employeeId),
                'rating' => $this->calculateWeeklyAverageRating($employeeId)
            ]
        ];

        // 8. Access Control for Teams (moved before stats calculation)
        // Alamin kung saang teams lang member ang employee.
        $employeeTeamIds = $employee ? $employee->helpdeskTeams->pluck('id')->toArray() : [];

        // Check kung may special permission ang user to view all teams
        $canViewAllTeams = $user->hasPermissionTo('can_view_tickets_even_not_employee');

        // 9. Teams Statistics Loop - Only for accessible teams
        // I-loop lang ang teams na pwede makita ng user para efficient
        $teams = HelpdeskTeam::all()
            ->filter(function ($team) use ($employeeTeamIds, $canViewAllTeams) {
                // Skip teams that user can't access
                $isMember = in_array($team->id, $employeeTeamIds);
                return $canViewAllTeams || $isMember;
            })
            ->map(function ($team) use ($employeeId, $visibleTickets, $user) {
            // Filter tickets para sa current Team sa loop
            $teamVisible = (clone $visibleTickets)->where('team_id', $team->id);
            
            // Team-wide stats
            $totalTickets = (clone $teamVisible)->count();
            $resolvedTickets = (clone $teamVisible)
                ->where('stage', 'Resolved')
                ->count();

            // 10. User Performance PER Team (with proper permission scope)
            // Ilang ticket ang natapos ng current user SA LOOB ng team na ito?
            $userClosed = $employeeId ? Ticket::query()->visibleTo($user)
                ->where('team_id', $team->id)
                ->where('employee_id', $employeeId)
                ->where('stage', 'Resolved')
                ->count() : 0;

            $userTotal = $employeeId ? Ticket::query()->visibleTo($user)
                ->where('team_id', $team->id)
                ->where('employee_id', $employeeId)
                ->count() : 0;

            // Calculate percentage success rate
            $userSuccess = $userTotal > 0 ? round(($userClosed / $userTotal) * 100) : 0;

            // Calculate rating (with proper permission scope)
            $userRating = $employeeId ? round(CustomerRating::whereHas('ticket', function ($query) use ($team, $employeeId, $user) {
                $query->visibleTo($user)
                    ->where('team_id', $team->id)
                    ->where('employee_id', $employeeId);
            })->avg('rating') ?? 0, 1) : 0;

            // 11. Queue Counts (Breakdown ng tickets sa team dashboard with proper scope)
            $userOpen = $employeeId ? Ticket::query()->visibleTo($user)
                ->where('team_id', $team->id)
                ->where('employee_id', $employeeId)
                ->where('stage', 'Open')
                ->count() : 0;

            $userUrgent = $employeeId ? Ticket::query()->visibleTo($user)
                ->where('team_id', $team->id)
                ->where('employee_id', $employeeId)
                ->where('priority', 'Urgent')
                ->count() : 0;

            $userFailed = $employeeId ? Ticket::query()->visibleTo($user)
                ->where('team_id', $team->id)
                ->where('employee_id', $employeeId)
                ->whereDate('deadline', '<', now())
                ->where('stage', '!=', 'Resolved')
                ->count() : 0;

            // I-return ang data structure para sa specific team na ito
            return [
                'id' => $team->id,
                'name' => $team->team_name,
                'ticketCount' => $totalTickets,
                'canView' => true, // Already filtered, all remaining teams are viewable
                'stats' => [
                    'closed' => $resolvedTickets,
                    'success' => $totalTickets > 0 
                        ? round(($resolvedTickets / $totalTickets) * 100) 
                        : 0,
                    'rating' => round(CustomerRating::whereHas('ticket', function ($query) use ($team) {
                        $query->where('team_id', $team->id);
                    })->avg('rating') ?? 0, 1)
                ],
                // Personal stats ko sa team na ito
                'userStats' => [
                    'closed' => $userClosed,
                    'success' => $userSuccess,
                    'rating' => $userRating,
                ],
                // Breakdown ng tickets sa team
                'queue' => [
                    'open' => (clone $teamVisible)->where('stage', 'Open')->count(),
                    'unassigned' => (clone $teamVisible)->whereNull('employee_id')->count(), // Walang nag-o-own
                    'urgent' => (clone $teamVisible)->where('priority', 'Urgent')->count(),
                    'failed' => (clone $teamVisible)->whereDate('deadline', '<', now())
                                ->where('stage', '!=', 'Resolved')->count(),
                    // Breakdown ng tickets ko sa team
                    'user' => [
                        'open' => $userOpen,
                        'unassigned' => 0, // N/A kasi assigned na sa akin
                        'urgent' => $userUrgent,
                        'failed' => $userFailed,
                    ]
                ]
            ];
        });

        // 12. Prepare Authorization Data
        // Kunin ang roles at permissions ng user para ipasa sa frontend (Inertia/Vue).
        $permissions = $user->roles()
            ->with('permissions')
            ->get()
            ->flatMap(fn($role) => $role->permissions->pluck('name'))
            ->unique()
            ->values()
            ->toArray();

        // Buuin ang User Object kasama ang roles, permissions at teams.
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

        // 13. Final Return
        // Ibalik ang Dashboard view gamit ang Inertia, bitbit ang lahat ng data.
        return Inertia::render('Dashboard', [
            'auth' => $authData,
            'stats' => $stats,
            'userStats' => $userStats,
            'globalStats' => $globalStats,
            'teams' => $teams,
        ]);
    }
}