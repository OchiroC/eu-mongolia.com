<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesCoverImage;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    use HandlesCoverImage;

    public function index(): Response
    {
        return Inertia::render('Admin/Events/Index', [
            'events' => Event::withCount('orders')
                ->withSum('ticketTypes', 'sold')
                ->latest('starts_at')
                ->paginate(15)
                ->through(fn ($e) => [
                    'id' => $e->id,
                    'title' => $e->title,
                    'cover_image' => $e->cover_image,
                    'city' => $e->city,
                    'status' => $e->status,
                    'starts_at' => $e->starts_at?->format('Y.m.d H:i'),
                    'orders_count' => $e->orders_count,
                    'sold' => (int) $e->ticket_types_sum_sold,
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Events/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data = $this->applyCover($request, $data, 'events');

        DB::transaction(function () use ($data, $request) {
            $event = Event::create([
                'organizer_id' => $request->user()->id,
                'title' => $data['title'],
                'slug' => $this->uniqueSlug($data['title']),
                'description' => $data['description'] ?? null,
                'cover_image' => $data['cover_image'] ?? null,
                'venue' => $data['venue'] ?? null,
                'city' => $data['city'] ?? null,
                'country' => $data['country'] ?? null,
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'] ?? null,
                'status' => $data['status'],
                'is_featured' => $data['is_featured'] ?? false,
                'has_tickets' => $data['has_tickets'] ?? false,
            ]);

            if ($data['has_tickets'] ?? false) {
                foreach ($data['ticket_types'] ?? [] as $t) {
                    $event->ticketTypes()->create([
                        'name' => $t['name'],
                        'price' => $t['price'],
                        'quantity' => $t['quantity'],
                    ]);
                }
            }
        });

        return redirect()->route('admin.events.index')->with('success', 'Эвент үүслээ.');
    }

    public function edit(Event $event): Response
    {
        $event->load('ticketTypes');

        return Inertia::render('Admin/Events/Edit', ['event' => $event]);
    }

    /** Эвентийн борлуулалт, тасалбар, оролцогчдын хяналт. */
    public function sales(Event $event): Response
    {
        $event->load('ticketTypes');

        $orders = $event->orders()
            ->with('tickets:id,order_id,status')
            ->latest()
            ->get();

        $paid = $orders->where('status', 'paid');
        $usedCount = $orders->flatMap->tickets->where('status', 'used')->count();
        $validCount = $orders->flatMap->tickets->where('status', 'valid')->count();

        return Inertia::render('Admin/Events/Sales', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'slug' => $event->slug,
                'status' => $event->status,
                'has_tickets' => $event->has_tickets,
                'starts_at' => $event->starts_at?->format('Y.m.d H:i'),
            ],
            'stats' => [
                'revenue' => (float) $paid->sum('total'),
                'orders_paid' => $paid->count(),
                'orders_pending' => $orders->where('status', 'pending')->count(),
                'sold' => (int) $event->ticketTypes->sum('sold'),
                'capacity' => (int) $event->ticketTypes->sum('quantity'),
                'checked_in' => $usedCount,
                'valid' => $validCount,
            ],
            'ticketTypes' => $event->ticketTypes->map(fn ($t) => [
                'name' => $t->name,
                'price' => (float) $t->price,
                'sold' => $t->sold,
                'quantity' => $t->quantity,
                'remaining' => $t->remaining(),
                'revenue' => (float) ($t->price * $t->sold),
            ]),
            'orders' => $orders->map(fn ($o) => [
                'id' => $o->id,
                'reference' => $o->reference,
                'buyer_name' => $o->buyer_name,
                'buyer_email' => $o->buyer_email,
                'total' => (float) $o->total,
                'status' => $o->status,
                'tickets' => $o->tickets->count(),
                'created_at' => $o->created_at->format('Y.m.d H:i'),
            ]),
        ]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $data = $this->validateData($request);
        $data = $this->applyCover($request, $data, 'events', $event->cover_image);

        // Тайлбараас хасагдсан контент зургуудыг storage-аас устгана.
        $this->deleteRemovedBodyImages($event->description, $data['description'] ?? $event->description);

        DB::transaction(function () use ($data, $event) {
            if ($data['title'] !== $event->title) {
                $data['slug'] = $this->uniqueSlug($data['title'], $event->id);
            }

            $event->update(collect($data)->except('ticket_types')->all());

            // Тасалбаргүй (мэдээллийн) эвент бол тасалбарын төрлийг боловсруулахгүй.
            $keepIds = [];
            if ($data['has_tickets'] ?? false) {
                foreach ($data['ticket_types'] ?? [] as $t) {
                    if (! empty($t['id'])) {
                        $type = $event->ticketTypes()->find($t['id']);
                        if ($type) {
                            $type->update(['name' => $t['name'], 'price' => $t['price'], 'quantity' => $t['quantity']]);
                            $keepIds[] = $type->id;
                        }
                    } else {
                        $new = $event->ticketTypes()->create([
                            'name' => $t['name'], 'price' => $t['price'], 'quantity' => $t['quantity'],
                        ]);
                        $keepIds[] = $new->id;
                    }
                }
            }

            // Үлдсэн (сонгогдоогүй эсвэл тасалбаргүй болсон) зарагдаагүй төрлүүдийг устгана.
            $event->ticketTypes()
                ->whereNotIn('id', $keepIds)
                ->where('sold', 0)
                ->delete();
        });

        return redirect()->route('admin.events.index')->with('success', 'Эвент шинэчлэгдлээ.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->deleteCoverUrl($event->cover_image);
        foreach ($this->bodyImageUrls($event->description) as $url) {
            $this->deleteCoverUrl($url);
        }
        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Эвент устгагдлаа.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:4096'],
            'remove_cover' => ['boolean'],
            'venue' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:64'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', 'in:draft,published,cancelled'],
            'is_featured' => ['boolean'],
            'has_tickets' => ['boolean'],
            'ticket_types' => ['array'],
            'ticket_types.*.id' => ['nullable', 'integer'],
            'ticket_types.*.name' => ['required', 'string', 'max:255'],
            'ticket_types.*.price' => ['required', 'numeric', 'min:0'],
            'ticket_types.*.quantity' => ['required', 'integer', 'min:0'],
        ]);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'event';
        $slug = $base;
        $i = 1;

        while (Event::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
