<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use App\Models\Ticket;
use App\Models\Employee;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('ticket.{ticketId}', function ($user, $ticketId) {
    $ticket = Ticket::find($ticketId);
    if (!$ticket) {
        Log::warning('Broadcasting auth failed: Ticket not found', ['ticketId' => $ticketId]);
        return false;
    }
    $isCustomer = $user->id === $ticket->customer_id;

    // Find employee by email
    $employee = Employee::where('email', $user->email)->first();
    $hasEmployee = (bool) $employee;

    // Check if the ticket is assigned to an employee that maps to this user
    $isAssigned = false;
    if ($ticket->assigned_to_employee_id) {
        $assigned = $ticket->assignedTo;
        if ($assigned && $assigned->user_id) {
            $isAssigned = $assigned->user_id === $user->id;
        }
    }

    // Check team membership (employees in the ticket's team)
    $isTeamMember = false;
    if ($ticket->team) {
        $teamUserIds = $ticket->team->employees()->pluck('user_id')->filter()->all();
        $isTeamMember = in_array($user->id, $teamUserIds);
    }

    $authorized = $isCustomer || $hasEmployee || $isAssigned || $isTeamMember;

    Log::info('Broadcasting auth check', [
        'user_id' => $user->id,
        'ticket_id' => $ticketId,
        'customer_id' => $ticket->customer_id,
        'is_customer' => $isCustomer,
        'has_employee' => $hasEmployee,
        'is_assigned' => $isAssigned,
        'is_team_member' => $isTeamMember,
        'authorized' => $authorized,
    ]);

    return $authorized;
});
