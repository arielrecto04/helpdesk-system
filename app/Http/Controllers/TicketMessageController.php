<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Events\NewMessageEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TicketMessageController extends Controller
{
    public function index(Request $request, Ticket $ticket)
    {
        $perPage = (int) $request->input('per_page', 20);
        $page = (int) $request->input('page', 1);

        // Fetch latest messages first
        $paginator = $ticket->messages()->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Reverse them so they appear oldest -> newest (top -> bottom) in the chat
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
        // 1. Validate
        $request->validate([
            'message' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|max:51200|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm,pdf,doc,docx,xls,xlsx,txt,zip,rar', // up to 50MB
        ]);

        // 2. Custom Guard: Ensure we have EITHER a message OR an attachment
        if (!$request->message && !$request->hasFile('attachment')) {
            return response()->json(['message' => 'Message or attachment is required.'], 422);
        }

        // 3. Handle File Upload and metadata
        $attachmentPath = null;
        $attachmentName = null;
        $attachmentType = null;
        $attachmentSize = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            // Save to storage/app/public/ticket-attachments
            $attachmentPath = $file->store('ticket-attachments', 'public');

            $attachmentName = $file->getClientOriginalName();
            $attachmentType = $file->getClientMimeType();
            $attachmentSize = $file->getSize();
        }

        // 4. Create Message with attachment metadata
        $message = $ticket->messages()->create([
            'user_id' => Auth::id(),
            'message' => $request->message,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_type' => $attachmentType,
            'attachment_size' => $attachmentSize,
        ]);

        // 5. Load User Relationship (for the avatar in Vue)
        $message->load('user');

        // 6. Broadcast to others
        broadcast(new NewMessageEvent($message))->toOthers();

        return response()->json($message, 201);
    }

    public function startCall(Request $request, Ticket $ticket)
    {
        // Validate call type
        $request->validate([
            'call_type' => 'required|in:voice,video',
        ]);

        // Create a call message
        $message = $ticket->messages()->create([
            'user_id' => Auth::id(),
            'message' => null, // No text message for calls
            'call_type' => $request->call_type,
            'call_status' => 'initiated',
            'call_started_at' => now(),
        ]);

        // Load user relationship
        $message->load('user');

        // Broadcast to others in the ticket
        broadcast(new NewMessageEvent($message))->toOthers();

        return response()->json($message, 201);
    }

    public function endCall(Request $request, Ticket $ticket, $messageId)
    {
        // Find the call message
        $message = $ticket->messages()->findOrFail($messageId);

        // Validate it's a call and not already ended
        if (!$message->call_type) {
            return response()->json(['message' => 'This message is not a call.'], 400);
        }

        if ($message->call_status === 'ended') {
            return response()->json(['message' => 'Call already ended.'], 400);
        }

        // Calculate duration if call was started
        $duration = 0;
        if ($message->call_started_at) {
            try {
                $started = Carbon::parse($message->call_started_at);
                $duration = (int) max(0, $started->diffInSeconds(now()));
            } catch (\Exception $ex) {
                $duration = 0;
            }
        }

        // Update call status
        $message->update([
            'call_status' => 'ended',
            'call_ended_at' => now(),
            'call_duration' => $duration,
        ]);

        // Reload the message
        $message->refresh();
        $message->load('user');

        // Broadcast update
        broadcast(new NewMessageEvent($message))->toOthers();

        return response()->json($message);
    }

    public function updateCallStatus(Request $request, Ticket $ticket, $messageId)
    {
        $request->validate([
            'status' => 'required|in:ringing,in_progress,ended,missed,declined',
        ]);

        $message = $ticket->messages()->findOrFail($messageId);

        if (!$message->call_type) {
            return response()->json(['message' => 'This message is not a call.'], 400);
        }

        $message->update([
            'call_status' => $request->status,
        ]);

        $message->refresh();
        $message->load('user');

        broadcast(new NewMessageEvent($message))->toOthers();

        return response()->json($message);
    }

    public function sendOffer(Request $request, Ticket $ticket, $messageId)
    {
        $request->validate([
            'offer' => 'required|array',
        ]);

        $message = $ticket->messages()->findOrFail($messageId);

        if (!$message->call_type) {
            return response()->json(['message' => 'This message is not a call.'], 400);
        }

        // Broadcast the offer to other participants
        broadcast(new \App\Events\CallSignalingEvent($message, 'offer', $request->offer, Auth::id()))->toOthers();

        return response()->json(['success' => true]);
    }

    public function sendAnswer(Request $request, Ticket $ticket, $messageId)
    {
        $request->validate([
            'answer' => 'required|array',
        ]);

        $message = $ticket->messages()->findOrFail($messageId);

        if (!$message->call_type) {
            return response()->json(['message' => 'This message is not a call.'], 400);
        }

        // Broadcast the answer to other participants
        broadcast(new \App\Events\CallSignalingEvent($message, 'answer', $request->answer, Auth::id()))->toOthers();

        return response()->json(['success' => true]);
    }

    public function sendIce(Request $request, Ticket $ticket, $messageId)
    {
        $request->validate([
            'candidate' => 'required|array',
        ]);

        $message = $ticket->messages()->findOrFail($messageId);

        if (!$message->call_type) {
            return response()->json(['message' => 'This message is not a call.'], 400);
        }

        // Broadcast the ICE candidate to other participants
        broadcast(new \App\Events\CallSignalingEvent($message, 'ice', $request->candidate, Auth::id()))->toOthers();

        return response()->json(['success' => true]);
    }
}