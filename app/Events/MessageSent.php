<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        // Create a unique channel for the conversation between these two users
        $userIds = [
            $this->message->sender_id,
            $this->message->receiver_id
        ];
        sort($userIds); // Sort to ensure consistent channel naming
        
        return new PrivateChannel('chat.' . $userIds[0] . '.' . $userIds[1]);
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'content' => $this->message->content,
                'created_at' => $this->message->created_at,
                'sender_id' => $this->message->sender_id,
                'receiver_id' => $this->message->receiver_id,
                'sender' => $this->message->sender,
                'receiver' => $this->message->receiver
            ]
        ];
    }
}
