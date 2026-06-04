<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RideController extends Controller
{
    public function index(Request $request): Response
    {
        $rides = Ride::active()
            ->upcoming()
            ->with('user:id,name')
            ->when($request->from, fn ($q, $v) => $q->where(fn ($w) => $w->where('from_city', 'like', "%{$v}%")->orWhere('from_country', 'like', "%{$v}%")))
            ->when($request->to, fn ($q, $v) => $q->where(fn ($w) => $w->where('to_city', 'like', "%{$v}%")->orWhere('to_country', 'like', "%{$v}%")))
            ->when($request->date, fn ($q, $d) => $q->whereDate('depart_at', $d))
            ->orderBy('depart_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($r) => $this->card($r));

        return Inertia::render('Rides/Index', [
            'rides' => $rides,
            'filters' => $request->only(['from', 'to', 'date']),
            'seo' => [
                'title' => 'Хамтдаа аялах — EU Mongolia',
                'description' => 'Европын хот, улс хооронд машин хуваалцан аялах зар. Зардлаа хуваая, замдаа хамтдаа.',
            ],
        ]);
    }

    public function show(Request $request, Ride $ride): Response
    {
        abort_unless($ride->status === 'active' || $ride->user_id === $request->user()?->id, 404);

        $ride->increment('views');
        $ride->load('user:id,name');

        return Inertia::render('Rides/Show', [
            'ride' => array_merge($this->card($ride), [
                'notes' => $ride->notes,
                'views' => $ride->views,
                'status' => $ride->status,
                'owned' => $request->user()?->id === $ride->user_id,
                'contact_phone' => $request->user() ? $ride->contact_phone : null,
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Rides/Form', ['countries' => $this->countries()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Ride::create($this->validateData($request) + ['user_id' => $request->user()->id, 'status' => 'active']);

        return redirect()->route('rides.my')->with('success', 'Аяллын зар нийтлэгдлээ.');
    }

    public function myRides(Request $request): Response
    {
        return Inertia::render('Rides/My', [
            'rides' => Ride::where('user_id', $request->user()->id)
                ->orderByDesc('depart_at')
                ->get()
                ->map(fn ($r) => array_merge($this->card($r), ['status' => $r->status])),
        ]);
    }

    public function edit(Request $request, Ride $ride): Response
    {
        $this->authorizeOwner($request, $ride);

        return Inertia::render('Rides/Form', [
            'ride' => array_merge($ride->toArray(), ['depart_at' => $ride->depart_at?->format('Y-m-d\TH:i')]),
            'countries' => $this->countries(),
        ]);
    }

    public function update(Request $request, Ride $ride): RedirectResponse
    {
        $this->authorizeOwner($request, $ride);
        $ride->update($this->validateData($request));

        return redirect()->route('rides.my')->with('success', 'Шинэчлэгдлээ.');
    }

    public function close(Request $request, Ride $ride): RedirectResponse
    {
        $this->authorizeOwner($request, $ride);
        $ride->update(['status' => $ride->status === 'closed' ? 'active' : 'closed']);

        return back()->with('success', 'Төлөв шинэчлэгдлээ.');
    }

    public function destroy(Request $request, Ride $ride): RedirectResponse
    {
        $this->authorizeOwner($request, $ride);
        $ride->delete();

        return back()->with('success', 'Устгагдлаа.');
    }

    /**
     * @return array<string, mixed>
     */
    private function card(Ride $r): array
    {
        return [
            'id' => $r->id,
            'from_city' => $r->from_city,
            'from_country' => $r->from_country,
            'to_city' => $r->to_city,
            'to_country' => $r->to_country,
            'depart_at' => $r->depart_at?->toIso8601String(),
            'seats' => $r->seats,
            'price' => $r->price,
            'user' => $r->user?->name ?? 'Хэрэглэгч',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function countries(): array
    {
        return ['Герман', 'Чех', 'Польш', 'Унгар', 'Австри', 'Франц', 'Бельги', 'Голланд', 'Швед', 'Итали', 'Испани'];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'from_city' => ['required', 'string', 'max:120'],
            'from_country' => ['nullable', 'string', 'max:64'],
            'to_city' => ['required', 'string', 'max:120'],
            'to_country' => ['nullable', 'string', 'max:64'],
            'depart_at' => ['required', 'date'],
            'seats' => ['required', 'integer', 'min:1', 'max:8'],
            'price' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
        ]);
    }

    private function authorizeOwner(Request $request, Ride $ride): void
    {
        abort_unless($ride->user_id === $request->user()->id, 403);
    }
}
