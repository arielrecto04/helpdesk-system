<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Events\NewMessageEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\TicketMessage;
use App\Models\Employee;
use App\Models\User;

class TicketMessageController extends Controller
{
    public function index(Request $request, Ticket $ticket)
    {
        /** @var User $user */
        $user = Auth::user();
        $employeeId = Employee::where('user_id', $user->id)->value('id');
        $isAssigned = $ticket->employee_id !== null && $employeeId !== null && $ticket->employee_id === $employeeId;
        $isTicketOwner = $user->customer && $ticket->customer_id === $user->customer->id;
        if (!(
            $isAssigned ||
            $user->hasPermissionTo('can_see_all_ticket_chat') ||
            $isTicketOwner
        )) {
            abort(403, 'Unauthorized');
        }
        $perPage = (int) $request->input('per_page', 20);
        $page = (int) $request->input('page', 1);
        $paginator = $ticket->messages()->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

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
        /** @var User $user */
        $user = Auth::user();
        $employeeId = Employee::where('user_id', $user->id)->value('id');
        $isAssigned = $ticket->employee_id !== null && $employeeId !== null && $ticket->employee_id === $employeeId;
        $isTicketOwner = $user->customer && $ticket->customer_id === $user->customer->id;
        if (!($isAssigned || $isTicketOwner)) {
            abort(403, 'Sorry your not assigned to reply on this conversation.');
        }
        $request->validate([
            'message' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|max:51200|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm,pdf,doc,docx,xls,xlsx,txt,zip,rar', // up to 50MB
        ]);
        if (!$request->message && !$request->hasFile('attachment')) {
            return response()->json(['message' => 'Message or attachment is required.'], 422);
        }
        $attachmentPath = null;
        $attachmentName = null;
        $attachmentType = null;
        $attachmentSize = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('ticket-attachments', 'public');
            $attachmentName = $file->getClientOriginalName();
            $attachmentType = $file->getClientMimeType();
            $attachmentSize = $file->getSize();
        }
        $message = $ticket->messages()->create([
            'user_id' => Auth::id(),
            'message' => $request->message,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_type' => $attachmentType,
            'attachment_size' => $attachmentSize,
        ]);
        $message->load('user');
        broadcast(new NewMessageEvent($message))->toOthers();
        return response()->json($message, 201);
    }

    public function downloadAttachment(TicketMessage $message)
    {
        /** @var User $user */
        $user = Auth::user();
        $employeeId = Employee::where('user_id', $user->id)->value('id');
        $ticket = $message->ticket()->first();
        $isAssigned = $ticket->employee_id !== null && $employeeId !== null && $ticket->employee_id === $employeeId;
        $isTicketOwner = $user->customer && $ticket->customer_id === $user->customer->id;
        if (!(
            $isAssigned ||
            $user->hasPermissionTo('can_see_all_ticket_chat') ||
            $isTicketOwner
        )) {
            abort(403, 'Unauthorized');
        }
        if (!$message->attachment_path) {
            abort(404);
        }
        $disk = Storage::disk('public');
        if (!$disk->exists($message->attachment_path)) {
            abort(404);
        }
        $filename = $message->attachment_name ?: basename($message->attachment_path);
        $fullPath = $disk->path($message->attachment_path);
        if (!file_exists($fullPath)) {
            abort(404);
        }
        return response()->download($fullPath, $filename);
    }
}