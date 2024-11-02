<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Events\MessageSent;

class MessageController extends Controller
{
    //
    public function store(Request $request, $friendId)
    {
        $message = Message::create([
            'content' => $request->content,
            'sender_id' => auth()->id(),
            'receiver_id' => $friendId
        ]);

        // Broadcast the message if you're using real-time updates
        broadcast(new MessageSent($message))->toOthers();

        return redirect()->back()->with('message', 'Message sent successfully');
    }
}
