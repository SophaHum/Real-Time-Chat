<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Message;
use App\Events\MessageSent;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Chat routes
    Route::get('/chat', function () {
        return Inertia::render('Chat/Index', [
            'users' => User::where('id', '!=', auth()->id())->get()
        ]);
    })->name('chat.index');

    Route::get('/chat/{friend}', function (User $friend) {
        return Inertia::render('Chat/Show', [
            'friend' => $friend,
            'messages' => Message::query()
                ->where(function ($query) use ($friend) {
                    $query->where('sender_id', auth()->id())
                        ->where('receiver_id', $friend->id);
                })
                ->orWhere(function ($query) use ($friend) {
                    $query->where('sender_id', $friend->id)
                        ->where('receiver_id', auth()->id());
                })
                ->with(['sender', 'receiver'])
                ->orderBy('created_at', 'asc')
                ->get()
        ]);
    })->name('chat.show');

    Route::post('/messages/{friend}', function (User $friend) {
        try {
            // Validate the request
            $validated = request()->validate([
                'content' => ['required', 'string', 'max:1000'],
            ]);

            // Create the message
            $message = Message::create([
                'sender_id' => auth()->id(),
                'receiver_id' => $friend->id,  // Make sure friend exists
                'content' => $validated['content']
            ]);

            // Debug logging
            \Log::info('Message created:', [
                'message_id' => $message->id,
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->receiver_id,
                'content' => $message->content
            ]);

            broadcast(new MessageSent($message))->toOthers();

            return redirect()->back()->with('message', 'Message sent successfully');

        } catch (\Exception $e) {
            \Log::error('Message creation failed:', [
                'error' => $e->getMessage(),
                'sender_id' => auth()->id(),
                'receiver_id' => $friend->id ?? null
            ]);

            return response()->json([
                'error' => 'Failed to send message'
            ], 422);
        }
    })->name('messages.store');
});

require __DIR__.'/auth.php';
