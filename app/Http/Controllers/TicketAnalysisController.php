<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\CustomerRating;
use App\Models\HelpdeskTeam;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TicketAnalysisExport;
use Carbon\Carbon;

class TicketAnalysisController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            $employee = Employee::where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->with('helpdeskTeams')
                ->first();

            // Build base query for visible tickets using the centralized visibility scope
            // (uses role-based permissions via User::hasPermissionTo).
            $visibleTicketsQuery = Ticket::query()->visibleTo($user);
            
            // Get all visible ticket IDs for efficient filtering
            $visibleTicketIds = $visibleTicketsQuery->pluck('id')->toArray();
            
            // Overall Statistics using single query with DB::raw
            $stageStats = Ticket::whereIn('id', $visibleTicketIds)
                ->select(
                    DB::raw('COUNT(*) as total'),
                    DB::raw("SUM(CASE WHEN stage = 'Open' THEN 1 ELSE 0 END) as open"),
                    DB::raw("SUM(CASE WHEN stage = 'In Progress' THEN 1 ELSE 0 END) as in_progress"),
                    DB::raw("SUM(CASE WHEN stage = 'Resolved' THEN 1 ELSE 0 END) as resolved"),
                    DB::raw("SUM(CASE WHEN stage = 'Closed' THEN 1 ELSE 0 END) as closed")
                )
                ->first();
            
            $totalTickets = $stageStats->total ?? 0;
            $openTickets = $stageStats->open ?? 0;
            $inProgressTickets = $stageStats->in_progress ?? 0;
            $resolvedTickets = $stageStats->resolved ?? 0;
            $closedTickets = $stageStats->closed ?? 0;
            
            // Priority Breakdown using single query
            $priorityBreakdown = Ticket::whereIn('id', $visibleTicketIds)
                ->select('priority', DB::raw('COUNT(*) as count'))
                ->groupBy('priority')
                ->pluck('count', 'priority')
                ->toArray();
            
            $priorityStats = [
                'low' => $priorityBreakdown['Low'] ?? 0,
                'normal' => $priorityBreakdown['Normal'] ?? 0,
                'high' => $priorityBreakdown['High'] ?? 0,
                'urgent' => $priorityBreakdown['Urgent'] ?? 0,
            ];
            
            // Stage Distribution for charts
            $stageDistribution = [
                ['stage' => 'Open', 'count' => $openTickets],
                ['stage' => 'In Progress', 'count' => $inProgressTickets],
                ['stage' => 'Resolved', 'count' => $resolvedTickets],
                ['stage' => 'Closed', 'count' => $closedTickets],
            ];
            
            // Monthly Ticket Trends (last 6 months) - optimized with single query
            $monthlyData = Ticket::whereIn('id', $visibleTicketIds)
                ->where('created_at', '>=', Carbon::now()->subMonths(6))
                ->select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                    DB::raw('COUNT(*) as created'),
                    DB::raw("SUM(CASE WHEN stage = 'Resolved' THEN 1 ELSE 0 END) as resolved")
                )
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->keyBy('month');
            
            $monthlyTrends = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $monthKey = $date->format('Y-m');
                $monthLabel = $date->format('M Y');
                
                $data = $monthlyData->get($monthKey);
                $monthlyTrends[] = [
                    'month' => $monthLabel,
                    'created' => $data->created ?? 0,
                    'resolved' => $data->resolved ?? 0,
                ];
            }
            
            // Team Performance - optimized with joins
            $teamStats = Ticket::whereIn('tickets.id', $visibleTicketIds)
                ->join('helpdesk_teams', 'tickets.team_id', '=', 'helpdesk_teams.id')
                ->select(
                    'helpdesk_teams.id',
                    'helpdesk_teams.team_name',
                    DB::raw('COUNT(tickets.id) as total_tickets'),
                    DB::raw("SUM(CASE WHEN tickets.stage = 'Resolved' THEN 1 ELSE 0 END) as resolved")
                )
                ->groupBy('helpdesk_teams.id', 'helpdesk_teams.team_name')
                ->having('total_tickets', '>', 0)
                ->get();
            
            // Get ratings for teams in one query
            $teamRatings = CustomerRating::join('tickets', 'customer_ratings.ticket_id', '=', 'tickets.id')
                ->whereIn('tickets.id', $visibleTicketIds)
                ->select('tickets.team_id', DB::raw('AVG(customer_ratings.rating) as avg_rating'))
                ->groupBy('tickets.team_id')
                ->pluck('avg_rating', 'team_id');
            
            $teams = $teamStats->map(function ($team) use ($teamRatings) {
                $successRate = $team->total_tickets > 0 
                    ? round(($team->resolved / $team->total_tickets) * 100) 
                    : 0;
                
                return [
                    'id' => $team->id,
                    'name' => $team->team_name,
                    'totalTickets' => $team->total_tickets,
                    'resolved' => $team->resolved,
                    'successRate' => $successRate,
                    'avgRating' => round($teamRatings[$team->id] ?? 0, 1),
                ];
            });
            
            // Average Response Time Calculation
            $avgResponseTime = $this->calculateAverageResponseTime($visibleTicketIds);

            // Employees list for filter (basic list)
            $employeesList = Employee::select('id', 'first_name', 'last_name')
                ->orderBy('first_name')
                ->get()
                ->map(function ($e) {
                    return [
                        'id' => $e->id,
                        'name' => trim($e->first_name . ' ' . $e->last_name),
                    ];
                })->toArray();
            
            // Recent Activity (last 10 tickets) with eager loading
            $recentTickets = Ticket::whereIn('id', $visibleTicketIds)
                ->with(['customer.company:id,name', 'team:id,team_name', 'assignedEmployee:id,first_name,last_name'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($ticket) {
                    $customerName = 'N/A';
                    if ($ticket->customer) {
                        if ($ticket->customer->company) {
                            $customerName = $ticket->customer->company->name;
                        } else {
                            $customerName = $ticket->customer->first_name . ' ' . $ticket->customer->last_name;
                        }
                    }

                    $assignedName = null;
                    if ($ticket->assignedEmployee) {
                        $assignedName = trim($ticket->assignedEmployee->first_name . ' ' . $ticket->assignedEmployee->last_name);
                    }

                    return [
                        'id' => $ticket->id,
                        'subject' => $ticket->subject,
                        'customer' => $customerName,
                        'team' => $ticket->team?->team_name ?? 'Unassigned',
                        'assigned_to' => $assignedName,
                        'stage' => $ticket->stage,
                        'priority' => $ticket->priority,
                        'deadline' => $ticket->deadline?->format('Y-m-d H:i'),
                        'created_at' => $ticket->created_at->format('Y-m-d H:i'),
                    ];
                });
            
            // SLA Compliance - improved calculation
            $slaStats = Ticket::whereIn('id', $visibleTicketIds)
                ->whereNotNull('deadline')
                ->select(
                    DB::raw('COUNT(*) as total'),
                    DB::raw("SUM(CASE WHEN (stage IN ('Resolved', 'Closed')) AND updated_at <= deadline THEN 1 ELSE 0 END) as on_time")
                )
                ->first();
            
            $slaCompliance = ($slaStats->total ?? 0) > 0 
                ? round(($slaStats->on_time / $slaStats->total) * 100) 
                : 0;
            
            // Customer Satisfaction - optimized query
            $customerSatisfaction = CustomerRating::join('tickets', 'customer_ratings.ticket_id', '=', 'tickets.id')
                ->whereIn('tickets.id', $visibleTicketIds)
                ->select(
                    DB::raw('AVG(customer_ratings.rating) as avg_rating'),
                    DB::raw('COUNT(*) as total_ratings')
                )
                ->first();
            
            $avgCustomerRating = round($customerSatisfaction->avg_rating ?? 0, 1);
            
            // Rating Distribution
            $ratingDistribution = CustomerRating::join('tickets', 'customer_ratings.ticket_id', '=', 'tickets.id')
                ->whereIn('tickets.id', $visibleTicketIds)
                ->select('customer_ratings.rating', DB::raw('COUNT(*) as count'))
                ->groupBy('customer_ratings.rating')
                ->orderBy('customer_ratings.rating', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'rating' => $item->rating,
                        'count' => $item->count,
                    ];
                });
            
            return Inertia::render('Ticket_Analysis', [
                'stats' => [
                    'total' => $totalTickets,
                    'open' => $openTickets,
                    'inProgress' => $inProgressTickets,
                    'resolved' => $resolvedTickets,
                    'closed' => $closedTickets,
                ],
                'priorityStats' => $priorityStats,
                'stageDistribution' => $stageDistribution,
                'monthlyTrends' => $monthlyTrends,
                'teams' => $teams,
                'employees' => $employeesList,
                'avgResponseTime' => $avgResponseTime,
                'recentTickets' => $recentTickets,
                'slaCompliance' => $slaCompliance,
                'avgCustomerRating' => $avgCustomerRating,
                'ratingDistribution' => $ratingDistribution,
            ]);
        } catch (\Exception $e) {
            Log::error('Ticket Analysis Error: ' . $e->getMessage());
            
            return Inertia::render('Ticket_Analysis', [
                'stats' => [
                    'total' => 0,
                    'open' => 0,
                    'inProgress' => 0,
                    'resolved' => 0,
                    'closed' => 0,
                ],
                'priorityStats' => ['low' => 0, 'normal' => 0, 'high' => 0, 'urgent' => 0],
                'stageDistribution' => [],
                'monthlyTrends' => [],
                'teams' => [],
                'employees' => [],
                'avgResponseTime' => 'N/A',
                'recentTickets' => [],
                'slaCompliance' => 0,
                'avgCustomerRating' => 0,
                'ratingDistribution' => [],
                'error' => 'Unable to load analytics data. Please try again later.',
            ]);
        }
    }

    /**
     * Export filtered ticket data as Excel (download)
     */
    public function export(Request $request)
    {
        $user = Auth::user();

        // Base visible tickets
        $query = Ticket::query()->visibleTo($user);

        // Apply team filter if provided and not 'all'
        $team = $request->query('team');
        if ($team && $team !== 'all') {
            $query->where('team_id', $team);
        }

        // Apply time range filter
        $range = $request->query('timeRange', '6months');
        if ($range === '6months') {
            $query->where('created_at', '>=', Carbon::now()->subMonths(6));
        } elseif ($range === '3months') {
            $query->where('created_at', '>=', Carbon::now()->subMonths(3));
        } elseif ($range === 'ytd') {
            $query->whereYear('created_at', Carbon::now()->year);
        } elseif ($range === 'all') {
            // no additional filter
        }

        // Apply priority filter
        $priority = $request->query('priority');
        if ($priority && $priority !== 'all') {
            $query->where('priority', $priority);
        }

        // Apply stage filter
        $stage = $request->query('stage');
        if ($stage && $stage !== 'all') {
            $query->where('stage', $stage);
        }

        // Date range filters (startDate, endDate)
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');
        if ($startDate) {
            try {
                $query->where('created_at', '>=', Carbon::parse($startDate)->startOfDay());
            } catch (\Exception $e) {
                // ignore invalid dates
            }
        }
        if ($endDate) {
            try {
                $query->where('created_at', '<=', Carbon::parse($endDate)->endOfDay());
            } catch (\Exception $e) {
                // ignore invalid dates
            }
        }

        // Employee filter (employee_id)
        $employee = $request->query('employee');
        if ($employee && $employee !== 'all') {
            $query->where('employee_id', $employee);
        }

        $tickets = $query->with(['customer.company', 'team', 'assignedEmployee:id,first_name,last_name'])->orderBy('created_at', 'desc')->get();

        $fileName = 'ticket-analysis-' . Carbon::now()->format('Y-m-d') . '.xlsx';

        // If Maatwebsite Excel is available and the Concerns interfaces exist, use it.
        if (class_exists(\Maatwebsite\Excel\Facades\Excel::class) && interface_exists('Maatwebsite\\Excel\\Concerns\\FromCollection')) {
            return Excel::download(new TicketAnalysisExport($tickets), $fileName);
        }

        // Fallback: generate CSV, convert to XLSX via Node script, and return the XLSX.
        $timestamp = Carbon::now()->format('YmdHis');
        $csvName = "ticket-analysis-{$timestamp}.csv";
        $xlsxName = "ticket-analysis-{$timestamp}.xlsx";
        $csvPath = storage_path('app/' . $csvName);
        $xlsxPath = base_path($xlsxName);

        // Write CSV
        $fh = fopen($csvPath, 'w');
        if ($fh === false) {
            abort(500, 'Unable to create export file.');
        }

        // Headings
        fputcsv($fh, (new TicketAnalysisExport($tickets))->headings());

        foreach ($tickets as $ticket) {
            fputcsv($fh, (new TicketAnalysisExport($tickets))->map($ticket));
        }

        fclose($fh);

        // Run Node conversion script
        $nodeScript = base_path('scripts/convert_csv_to_xlsx.cjs');
        $cmd = escapeshellcmd("node " . escapeshellarg($nodeScript) . " " . escapeshellarg($csvPath) . " " . escapeshellarg($xlsxPath));
        exec($cmd . " 2>&1", $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($xlsxPath)) {
            Log::error('XLSX conversion failed', ['cmd' => $cmd, 'output' => $output]);
            // Fall back to streaming CSV download with .csv extension
            return response()->download($csvPath, str_replace('.xlsx', '.csv', $fileName))->deleteFileAfterSend(true);
        }

        // Return XLSX and delete both files after sending
        return response()->download($xlsxPath, $fileName)->deleteFileAfterSend(true);
    }
    
    /**
     * Calculate average response time based on ticket messages
     */
    private function calculateAverageResponseTime($ticketIds)
    {
        if (empty($ticketIds)) {
            return 'N/A';
        }
        
        // Calculate time between ticket creation and first response from an employee/agent
        // (employee = user with an employee record, not a customer)
        $responseData = DB::table('tickets')
            ->join('ticket_messages', 'tickets.id', '=', 'ticket_messages.ticket_id')
            ->join('employees', 'ticket_messages.user_id', '=', 'employees.user_id')
            ->whereIn('tickets.id', $ticketIds)
            ->select(
                'tickets.id',
                DB::raw('MIN(ticket_messages.created_at) as first_response')
            )
            ->groupBy('tickets.id')
            ->get();
        
        if ($responseData->isEmpty()) {
            return 'N/A';
        }
        
        $totalMinutes = 0;
        $count = 0;
        
        foreach ($responseData as $data) {
            $ticket = Ticket::find($data->id);
            if ($ticket && $data->first_response) {
                $responseTime = Carbon::parse($ticket->created_at)
                    ->diffInMinutes(Carbon::parse($data->first_response));
                $totalMinutes += $responseTime;
                $count++;
            }
        }
        
        if ($count === 0) {
            return 'N/A';
        }
        
        $avgMinutes = round($totalMinutes / $count);
        
        // Format as human-readable time
        if ($avgMinutes < 60) {
            return $avgMinutes . 'm';
        } elseif ($avgMinutes < 1440) {
            $hours = round($avgMinutes / 60, 1);
            return $hours . 'h';
        } else {
            $days = round($avgMinutes / 1440, 1);
            return $days . 'd';
        }
    }
}
