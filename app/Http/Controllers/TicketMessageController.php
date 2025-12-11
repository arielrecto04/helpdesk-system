<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Events\NewMessageEvent;
use Illuminate\Support\Facades\Auth;

class TicketMessageController extends Controller
{
    public function index(Request $request, Ticket $ticket)
    {
        $perPage = (int) $request->input('per_page', 20);
        $page = (int) $request->input('page', 1);

        $paginator = $ticket->messages()->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Return items in chronological order (oldest -> newest) for the page
        $items = collect($paginator->items())->reverse()->values()->all();

        return response()->json([
            'data' => $items,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ]);
    }

    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);
        $message = $ticket->messages()->create([
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);


        // Load user info para sa frontend display (avatar/name)
        $message->load('user');

        // Send to Reverb/Pusher (don't broadcast back to sender)
        broadcast(new NewMessageEvent($message))->toOthers();

        // If the request expects JSON (XHR), return the created message so the sender
        // can receive the canonical record (id, timestamps) for optimistic UI replacement.
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($message, 201);
        }

        return back();
    }
}
