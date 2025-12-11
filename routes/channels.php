<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Ticket;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('ticket.{ticketId}', function ($user, $ticketId) {
    $ticket = Ticket::find($ticketId);
    if (!$ticket) {
        return false;
    }
    // Logic: Pwede lang makita kung ikaw ang customer, o may employee record ka (admin/agent)
    return $user->id === $ticket->customer_id || \App\Models\Employee::where('user_id', $user->id)->exists();
});
