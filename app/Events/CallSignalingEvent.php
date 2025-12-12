<?php

namespace App\Events;

use App\Models\TicketMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // Change this import
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// Implement ShouldBroadcastNow instead of ShouldBroadcast
class CallSignalingEvent implements ShouldBroadcastNow 
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $type; // 'offer', 'answer', or 'ice'
    public $data; // SDP or ICE candidate data
    public $senderId;

    /**
     * Create a new event instance.
     */
    public function __construct(TicketMessage $message, string $type, array $data, int $senderId)
    {
        $this->message = $message;
        $this->type = $type;
        $this->data = $data;
        $this->senderId = $senderId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('ticket.' . $this->message->ticket_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'CallSignalingEvent';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'type' => $this->type,
            'data' => $this->data,
            'sender_id' => $this->senderId,
        ];
    }
}