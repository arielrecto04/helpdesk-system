<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'totalTickets' => Ticket::count(),
            'openTickets' => Ticket::where('status', 'New')->count(),
            'resolvedToday' => Ticket::where('status', 'Resolved')
                ->whereDate('updated_at', today())
                ->count(),
            'averageResponseTime' => '2.5h' // You'll need to implement this calculation
        ];

        $recentActivities = Ticket::latest()
            ->take(5)
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'type' => $ticket->type,
                    'title' => $ticket->title,
                    'status' => $ticket->status,
                    'time' => $ticket->created_at->diffForHumans()
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentActivities' => $recentActivities
        ]);
    }
}