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
    // Call-related endpoints removed: voice/video calling is disabled.
}