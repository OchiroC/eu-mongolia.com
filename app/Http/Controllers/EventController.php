<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Order;
use App\Models\TicketType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(Request $request): Response
    {
        $events = Event::published()
            ->withMin('ticketTypes', 'price')
            ->when($request->country, fn ($q, $c) => $q->where('country', $c))
            ->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->orderBy('starts_at')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Events/Index', [
            'events' => $events,
            'filters' => $request->only(['country', 'search']),
        ]);
    }

    public function show(Event $event): Response
    {
        abort_unless($event->status === 'published', 404);

        $event->load(['ticketTypes', 'organizer:id,name']);

        return Inertia::render('Events/Show', [
            'event' => $event,
            'ticketTypes' => $event->ticketTypes->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'price' => $t->price,
                'remaining' => $t->remaining(),
            ]),
            'seo' => [
                'title' => $event->title,
                'description' => Str::limit(strip_tags((string) $event->description), 150),
                'image' => $event->cover_image,
                'url' => url("/events/{$event->slug}"),
                'jsonld' => array_filter([
                    '@context' => 'https://schema.org',
                    '@type' => 'Event',
                    'name' => $event->title,
                    'description' => Str::limit(strip_tags((string) $event->description), 250),
                    'image' => $event->cover_image,
                    'startDate' => $event->starts_at?->toAtomString(),
                    'endDate' => $event->ends_at?->toAtomString(),
                    'eventStatus' => 'https://schema.org/EventScheduled',
                    'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
                    'location' => array_filter([
                        '@type' => 'Place',
                        'name' => $event->venue,
                        'address' => trim(implode(', ', array_filter([$event->city, $event->country]))) ?: null,
                    ]),
                    'organizer' => $event->organizer ? ['@type' => 'Organization', 'name' => $event->organizer->name] : null,
                    'url' => url("/events/{$event->slug}"),
                ]),
            ],
        ]);
    }

    /**
     * Тасалбар захиалах — pending order үүсгэнэ (төлбөр хийгдээгүй).
     */
    public function checkout(Request $request, Event $event): RedirectResponse
    {
        abort_unless($event->status === 'published', 404);
        abort_unless($event->has_tickets, 404); // мэдээллийн эвентэд тасалбар байхгүй

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.ticket_type_id' => ['required', 'exists:ticket_types,id'],
            'items.*.quantity' => ['required', 'integer', 'min:0', 'max:20'],
            'buyer_name' => ['required', 'string', 'max:255'],
            'buyer_email' => ['required', 'email', 'max:255'],
        ]);

        $order = DB::transaction(function () use ($data, $event, $request) {
            $total = 0;
            $lines = [];

            foreach ($data['items'] as $item) {
                $qty = (int) $item['quantity'];
                if ($qty < 1) {
                    continue;
                }

                /** @var TicketType $type */
                $type = TicketType::where('event_id', $event->id)
                    ->lockForUpdate()
                    ->findOrFail($item['ticket_type_id']);

                abort_if($type->remaining() < $qty, 422, "{$type->name}: үлдэгдэл хүрэлцэхгүй байна.");

                $total += (float) $type->price * $qty;
                $lines[] = [$type, $qty];
            }

            abort_if(empty($lines), 422, 'Дор хаяж нэг тасалбар сонгоно уу.');

            $order = Order::create([
                'user_id' => $request->user()->id,
                'event_id' => $event->id,
                'reference' => 'ORD-'.strtoupper(Str::random(8)),
                'total' => $total,
                'status' => 'pending',
                'buyer_name' => $data['buyer_name'],
                'buyer_email' => $data['buyer_email'],
            ]);

            // Тоо ширхэгийг нөөцлөж, тасалбар бүрийг үүсгэнэ.
            foreach ($lines as [$type, $qty]) {
                $type->increment('sold', $qty);
                $order->tickets()->createMany(
                    collect(range(1, $qty))->map(fn () => [
                        'ticket_type_id' => $type->id,
                        'code' => (string) Str::uuid(),
                        'status' => 'valid',
                    ])->all()
                );
            }

            return $order;
        });

        return redirect()->route('orders.show', $order)
            ->with('success', 'Захиалга үүслээ. Төлбөрөө хийнэ үү.');
    }
}
