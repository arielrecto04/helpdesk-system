<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\HelpdeskTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Database\Eloquent\Builder;

class TeamTicketsController extends Controller
{
    /**
     * Display a paginated list of tickets for a team.
     *
     * @param Request $request
     * @param int $teamId
     * @return Response
     */
    public function index(Request $request, $teamId): Response
    {
        /** @var User $user */
        $user = Auth::user();
        $team = HelpdeskTeam::findOrFail($teamId);
        
        // Check if user is admin
        $isAdmin = $user->hasRole('admin');
        
        // Initialize query with relationships
        $query = Ticket::query()
            ->with(['customer', 'team', 'assignedTo']) // <-- ITO YUNG FIX
            ->where('team_id', $team->id);

        if (!$isAdmin) {
            // Verify user belongs to the team
            $hasTeamAccess = $user->teams()
                ->where('helpdesk_teams.id', $teamId)
                ->exists();

            if (!$hasTeamAccess) {
                abort(403, 'Unauthorized. You can only view tickets from your teams.');
            }
        }

        // Get paginated results
        $tickets = $query
            ->with('assignedTo') // Siguraduhin na nandito pa rin ito
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // --- START FINAL FIX ---
        // Manwal nating idadagdag ang pangalan sa bawat ticket object
        // para i-bypass ang anumang serialization issue sa User model.
        $tickets->getCollection()->transform(function ($ticket) {
            if ($ticket->assignedTo) {
                // Gagawa tayo ng bagong attribute na 'assigned_user_name'
                $ticket->assigned_user_name = $ticket->assignedTo->first_name . ' ' . $ticket->assignedTo->last_name;
            } else {
                $ticket->assigned_user_name = null;
            }
            return $ticket;
        });
        // --- END FINAL FIX ---

        return Inertia::render('TeamTickets', [
            'tickets' => $tickets,
            'team' => $team,
            'isAdmin' => $isAdmin,
            'filters' => $request->only(['search', 'status', 'priority'])
        ]);
    }
}

