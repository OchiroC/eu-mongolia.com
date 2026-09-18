<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use App\Models\Parcel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ParcelController extends Controller
{
    public function index(Request $request): Response
    {
        $parcels = Parcel::active()
            ->with(['user:id,name', 'flight'])
            ->where(fn ($q) => $q->whereNull('travel_date')->orWhere('travel_date', '>=', now()->toDateString()))
            ->when(in_array($request->type, array_keys(Parcel::TYPES), true), fn ($q) => $q->where('type', $request->type))
            ->when(in_array($request->direction, array_keys(Parcel::DIRECTIONS), true), fn ($q) => $q->where('direction', $request->direction))
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($p) => self::card($p));

        return Inertia::render('Parcels/Index', [
            'parcels' => $parcels,
            'filters' => $request->only(['type', 'direction']),
            'types' => Parcel::TYPES,
            'directions' => Parcel::DIRECTIONS,
            'seo' => [
                'title' => 'Ачаа, илгээмж | '.config('app.name'),
                'description' => 'Монгол, Германы хооронд ачаа, илгээмж авч явах хүн олох эсвэл ачааныхаа сул зайг санал болгох.',
            ],
        ]);
    }

    public function show(Request $request, Parcel $parcel): Response
    {
        abort_unless($parcel->status === 'active' || $parcel->user_id === $request->user()?->id, 404);

        $parcel->increment('views');
        $parcel->load(['user:id,name', 'flight']);

        return Inertia::render('Parcels/Show', [
            'parcel' => array_merge(self::card($parcel), [
                'description' => $parcel->description,
                'views' => $parcel->views,
                'status' => $parcel->status,
                'owned' => $request->user()?->id === $parcel->user_id,
                // Утсыг зөвхөн нэвтэрсэн хэрэглэгчид харуулна.
                'contact_phone' => $request->user() ? $parcel->contact_phone : null,
            ]),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Parcels/Form', $this->formOptions() + [
            'preset' => [
                'flight_id' => Flight::where('slug', $request->flight)->value('id'),
                'type' => in_array($request->type, array_keys(Parcel::TYPES), true) ? $request->type : 'offer',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $parcel = Parcel::create($this->validateData($request) + ['user_id' => $request->user()->id, 'status' => 'active']);

        return redirect()->route('parcels.show', $parcel)->with('success', 'Зар нийтлэгдлээ.');
    }

    public function my(Request $request): Response
    {
        return Inertia::render('Parcels/My', [
            'parcels' => Parcel::where('user_id', $request->user()->id)->with('flight')->latest()->get()
                ->map(fn ($p) => self::card($p) + ['status' => $p->status, 'views' => $p->views]),
        ]);
    }

    public function edit(Request $request, Parcel $parcel): Response
    {
        $this->authorizeOwner($request, $parcel);

        return Inertia::render('Parcels/Form', $this->formOptions() + [
            'parcel' => $parcel->only(['id', 'flight_id', 'type', 'direction', 'from_city', 'to_city', 'weight_kg', 'price', 'description', 'contact_phone'])
                + ['travel_date' => $parcel->travel_date?->format('Y-m-d')],
        ]);
    }

    public function update(Request $request, Parcel $parcel): RedirectResponse
    {
        $this->authorizeOwner($request, $parcel);
        $parcel->update($this->validateData($request));

        return redirect()->route('parcels.show', $parcel)->with('success', 'Зар шинэчлэгдлээ.');
    }

    public function close(Request $request, Parcel $parcel): RedirectResponse
    {
        $this->authorizeOwner($request, $parcel);
        $parcel->update(['status' => $parcel->status === 'closed' ? 'active' : 'closed']);

        return back()->with('success', 'Төлөв шинэчлэгдлээ.');
    }

    public function destroy(Request $request, Parcel $parcel): RedirectResponse
    {
        $this->authorizeOwner($request, $parcel);
        $parcel->delete();

        return redirect()->route('parcels.my')->with('success', 'Зар устгагдлаа.');
    }

    /**
     * @return array<string, mixed>
     */
    public static function card(Parcel $p): array
    {
        return [
            'id' => $p->id,
            'type' => $p->type,
            'type_label' => Parcel::TYPES[$p->type] ?? $p->type,
            'direction' => $p->direction,
            'direction_label' => Parcel::DIRECTIONS[$p->direction] ?? $p->direction,
            'from_city' => $p->from_city,
            'to_city' => $p->to_city,
            'weight_kg' => $p->weight_kg,
            'price' => $p->price,
            'date' => $p->flight ? $p->flight->localTime()->format('Y-m-d') : $p->travel_date?->format('Y-m-d'),
            'flight' => $p->flight ? ['code' => $p->flight->code, 'slug' => $p->flight->slug] : null,
            'excerpt' => str($p->description)->limit(140)->toString(),
            'user' => $p->user?->name ?? 'Хэрэглэгч',
            'created_at' => $p->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'types' => Parcel::TYPES,
            'directions' => Parcel::DIRECTIONS,
            // Сонгох боломжтой ирээдүйн нислэгүүд (цуцлагдаагүй).
            'flights' => Flight::upcoming()->where('status', '!=', 'cancelled')->orderBy('scheduled_at')->take(40)->get()
                ->map(fn ($f) => [
                    'id' => $f->id,
                    'direction' => $f->direction,
                    'label' => $f->code.' · '.$f->localTime()->format('Y.m.d H:i').' · '.$f->origin.' → '.$f->destination,
                ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(Parcel::TYPES))],
            'direction' => ['required', Rule::in(array_keys(Parcel::DIRECTIONS))],
            'flight_id' => ['nullable', 'exists:flights,id'],
            'travel_date' => ['nullable', 'date', 'required_without:flight_id'],
            'from_city' => ['required', 'string', 'max:120'],
            'to_city' => ['required', 'string', 'max:120'],
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'price' => ['nullable', 'string', 'max:60'],
            'description' => ['required', 'string', 'max:2000'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
        ], [
            'travel_date.required_without' => 'Нислэг эсвэл явах өдрөө сонгоно уу.',
        ]);

        if (! empty($data['flight_id'])) {
            $data['travel_date'] = null;
        }

        return $data;
    }

    private function authorizeOwner(Request $request, Parcel $parcel): void
    {
        abort_unless($parcel->user_id === $request->user()->id, 403);
    }
}
