<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Listing;
use App\Notifications\NewMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MessageController extends Controller
{
    /** Inbox — хэрэглэгчийн бүх яриа. */
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;

        $conversations = Conversation::forUser($userId)
            ->with(['listing:id,title,slug', 'buyer:id,name,avatar_path', 'seller:id,name,avatar_path'])
            ->withCount(['messages as unread' => fn ($q) => $q->whereNull('read_at')->where('sender_id', '!=', $userId)])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function ($c) use ($userId) {
                $other = $c->otherUser($userId);
                $last = $c->messages()->latest('id')->first();

                return [
                    'id' => $c->id,
                    'other' => $other?->name ?? 'Хэрэглэгч',
                    'avatar' => $other?->avatar_url,
                    'listing' => $c->listing ? ['title' => $c->listing->title, 'slug' => $c->listing->slug] : null,
                    'last' => $last?->body,
                    'last_at' => $c->last_message_at?->diffForHumans(),
                    'unread' => $c->unread,
                ];
            });

        return Inertia::render('Messages/Index', ['conversations' => $conversations]);
    }

    /** Ярианы дэлгэрэнгүй (мессежүүд) + уншсан болгох. */
    public function show(Request $request, Conversation $conversation): Response
    {
        $userId = $request->user()->id;
        abort_unless($conversation->hasParticipant($userId), 403);

        // Нөгөө талын уншаагүй мессежийг уншсан болгоно.
        $conversation->messages()->whereNull('read_at')->where('sender_id', '!=', $userId)->update(['read_at' => now()]);

        $conversation->load(['listing:id,title,slug,price,price_type', 'buyer:id,name,avatar_path', 'seller:id,name,avatar_path']);
        $other = $conversation->otherUser($userId);

        return Inertia::render('Messages/Show', [
            'conversation' => [
                'id' => $conversation->id,
                'other' => $other?->name ?? 'Хэрэглэгч',
                'avatar' => $other?->avatar_url,
                'listing' => $conversation->listing ? [
                    'title' => $conversation->listing->title,
                    'slug' => $conversation->listing->slug,
                ] : null,
            ],
            'messages' => $conversation->messages()->with('sender:id,name')->oldest('id')->get()->map(fn ($m) => [
                'id' => $m->id,
                'body' => $m->body,
                'mine' => $m->sender_id === $userId,
                'sender' => $m->sender?->name,
                'created_at' => $m->created_at->format('Y.m.d H:i'),
            ]),
        ]);
    }

    /** Ярианд хариу бичих. */
    public function reply(Request $request, Conversation $conversation): RedirectResponse
    {
        $userId = $request->user()->id;
        abort_unless($conversation->hasParticipant($userId), 403);

        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $message = $conversation->messages()->create([
            'sender_id' => $userId,
            'body' => $data['body'],
        ]);
        $conversation->update(['last_message_at' => now()]);

        $conversation->otherUser($userId)?->notify(new NewMessage($message));

        return back();
    }

    /** Зарын хуудаснаас худалдагчид анхны зурвас илгээх. */
    public function start(Request $request, Listing $listing): RedirectResponse
    {
        $buyerId = $request->user()->id;

        if ($listing->user_id === $buyerId) {
            return back()->with('error', 'Өөрийн зар дээр мессеж бичих боломжгүй.');
        }

        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $conversation = Conversation::firstOrCreate(
            ['listing_id' => $listing->id, 'buyer_id' => $buyerId],
            ['seller_id' => $listing->user_id],
        );

        $message = $conversation->messages()->create([
            'sender_id' => $buyerId,
            'body' => $data['body'],
        ]);
        $conversation->update(['last_message_at' => now()]);

        $conversation->seller?->notify(new NewMessage($message));

        return redirect()->route('messages.show', $conversation)
            ->with('success', 'Зурвас илгээгдлээ.');
    }
}
