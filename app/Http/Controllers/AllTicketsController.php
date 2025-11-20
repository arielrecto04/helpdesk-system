<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Inertia\Inertia;
use Inertia\Response;

class AllTicketsController extends Controller
{
    public function index(): Response
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = Ticket::query()
            ->with(['customer', 'team', 'assignedTo'])
            ->latest();

        // Only show all tickets when user is admin or has broad view permissions
        $hasBroadView = $user->hasRole('admin')
            || $user->hasPermissionTo('view_all_tickets')
            || $user->hasPermissionTo('can_view_other_teams_tickets')
            || $user->hasPermissionTo('can_view_other_locations_tickets')
            || $user->hasPermissionTo('can_view_other_users_tickets');

        if (!$hasBroadView) {
            $teamIds = $user->teams()->pluck('helpdesk_teams.id')->toArray();
            $query->where(function ($q) use ($teamIds, $user) {
                $q->whereIn('team_id', $teamIds)
                    ->orWhere('assigned_to_user_id', $user->id);
            });
        }

        $alltickets = $query->paginate(15)->withQueryString();

        return Inertia::render('AllTickets', [
            'tickets' => $alltickets,
            'pageTitle' => 'All Tickets'
        ]);
    }
}