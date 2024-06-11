<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Response;
use Carbon\Carbon;

class MessageController extends Controller
{
    public function index(): Response
    {
        return inertia('Chat/Index', [
            'users' => $this->getUsers()
        ]);
    }

    public function show(User $user): Response
    {
        $authUserId = auth()->user()->id;

        $unseenMessages = $user->sendMessage()
            ->where('receiver_id', $authUserId)
            ->whereNull('seen_at')
            ->get();

        $unseenMessages->each->update(['seen_at' => now()]);

        $lastSeenAt = $this->formatLastSeen($user->last_seen_at);

        $messages = Message::query()
            ->where(fn ($q) => $q->where('sender_id', $authUserId)->where('receiver_id', $user->id))
            ->orWhere(fn ($q) => $q->where('sender_id', $user->id)->where('receiver_id', $authUserId))
            ->get()
            ->groupBy(fn ($message) => $this->formatMessageDate($message->created_at))
            ->map(fn ($messages, $date) => [
                'messages' => $messages,
                'date' => $date
            ])->values();

        return inertia('Chat/Show', [
            'chat_with' => [
                'id' => $user->id,
                'uuid' => $user->uuid,
                'name' => $user->name,
                'last_seen_at' => $lastSeenAt,
            ],
            'messages' => $messages,
            'users' => $this->getUsers()
        ]);
    }

    public function store(User $user, Request $request)
    {
        /** @var User $authUser */
        $authUser = auth()->user();

        $message = $authUser->sendMessage()->create([
            'content' => $request->message,
            'receiver_id' => $user->id,
        ]);

        broadcast(new MessageSent($message->load('receiver')))->toOthers();

        return back();
    }

    public function destroy(Message $message)
    {
        if ($message->sender_id !== auth()->id()) {
            abort(403);
        }

        $message->update(['deleted_at' => now()]);

        return back();
    }

    private function getUsers()
    {
        $authUserId = auth()->user()->id;

        return User::query()
            ->whereHas('sendMessage', fn ($query) => $query->where('receiver_id', $authUserId))
            ->orWhereHas('receiveMessage', fn ($query) => $query->where('sender_id', $authUserId))
            ->withCount(['sendMessage' => fn ($query) => $query->where('receiver_id', $authUserId)->whereNull('seen_at')])
            ->with([
                'sendMessage' => fn ($query) => $query->whereIn('id', function ($query) use ($authUserId) {
                    $query->selectRaw('max(id)')
                        ->from('messages')
                        ->where('receiver_id', $authUserId)
                        ->groupBy('sender_id');
                }),
                'receiveMessage' => fn ($query) => $query->whereIn('id', function ($query) use ($authUserId) {
                    $query->selectRaw('max(id)')
                        ->from('messages')
                        ->where('sender_id', $authUserId)
                        ->groupBy('receiver_id');
                }),
            ])
            ->orderByDesc(function ($query) {
                $query->select('created_at')
                    ->from('messages')
                    ->whereColumn('sender_id', 'users.id')
                    ->orWhereColumn('receiver_id', 'users.id')
                    ->orderByDesc('created_at')
                    ->limit(1);
            })
            ->get();
    }

    private function formatLastSeen(Carbon $lastSeen): string
    {
        if ($lastSeen->isToday()) {
            return 'last seen today at ' . $lastSeen->format('H:i');
        }

        if ($lastSeen->isYesterday()) {
            return 'last seen yesterday at ' . $lastSeen->format('H:i');
        }

        return 'last seen at ' . $lastSeen->format('d/m/Y H:i');
    }

    private function formatMessageDate(Carbon $date): string
    {
        if ($date->isToday()) {
            return 'Today';
        }

        if ($date->isYesterday()) {
            return 'Yesterday';
        }

        return $date->format('F j, Y');
    }
}
