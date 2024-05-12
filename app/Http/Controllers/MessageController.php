<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Response;

class MessageController extends Controller
{
    public function index(): Response
    {
        return inertia('Chat/Index', [
            "users" => $this->getUser()
        ]);
    }

    public function show(User $user): Response
    {
        return inertia('Chat/Show', [
            "chat_with" => $user,
            "messages" => Message::query()
                ->where(fn ($q) => $q->where('sender_id', auth()->user()->id)->where('receiver_id', $user->id))
                ->orWhere(fn ($q) => $q->where('sender_id', $user->id)->where('receiver_id', auth()->user()->id))
                ->get()
                ->groupBy(function ($message) {
                    return $message->created_at->isToday() ? "Today" : ($message->created_at->isYesterday() ? "Yesterday" : $message->created_at->format("F j, Y"));
                })
                ->map(function ($messages, $date) {
                    return [
                        "messages" => $messages,
                        "date" => $date
                    ];
                })
                ->values(),
            "users" => $this->getUser()
        ]);
    }

    public function store(User $user, Request $request)
    {
        /** @var User $authUser */
        $authUser = auth()->user();
        $authUser->sendMessage()->create([
            "content" => $request->message,
            "receiver_id" => $user->id,
        ]);

        broadcast(new MessageSent($request->message))->toOthers();

        return back();
    }

    public function destroy(Message $message)
    {
        if ($message->sender_id !== auth()->id()) {
            abort(403);
        }

        tap($message)->update([
            'deleted_at' => now(),
        ]);

        return back();
    }

    private function getUser()
    {
        return User::query()
                ->where('id', '!=', auth()->user()->id)
                ->get();
    }
}
