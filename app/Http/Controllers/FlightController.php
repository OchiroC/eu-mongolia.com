<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use App\Models\Guide;
use App\Models\Parcel;
use App\Models\Ride;
use App\Support\FlightShareImage;
use App\Support\Transit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FlightController extends Controller
{
    public function index(): Response
    {
        $flights = Flight::upcoming()
            ->withCount(['passengers', 'rides' => fn ($q) => $q->where('status', 'active'), 'parcels' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('scheduled_at')
            ->take(40)
            ->get();

        return Inertia::render('Flights/Index', [
            'arrivals' => $flights->where('direction', 'arrival')->take(12)->map->card()->values(),
            'departures' => $flights->where('direction', 'departure')->take(12)->map->card()->values(),
            'seo' => [
                'title' => 'Нислэгийн самбар | '.config('app.name'),
                'description' => 'Улаанбаатар, Франкфуртын хоорондох МИАТ-ын нислэг. Нислэг бүрээр угтах хүн, хамт явах машин, ачаа авч явах хүн олоорой.',
            ],
        ]);
    }

    public function show(Request $request, Flight $flight): Response
    {
        $flight->loadCount('passengers');
        $user = $request->user();
        $me = $user ? $flight->passengers()->whereKey($user->id)->first() : null;

        $rides = $flight->rides()->active()->with('user:id,name')->orderBy('depart_at')->get()
            ->map(fn (Ride $r) => [
                'id' => $r->id,
                'from_city' => $r->from_city,
                'to_city' => $r->to_city,
                'depart_at' => $r->depart_at?->toIso8601String(),
                'seats' => $r->seats,
                'price' => $r->price,
                'user' => $r->user?->name ?? 'Хэрэглэгч',
            ]);

        $parcels = $flight->parcels()->active()->with('user:id,name')->latest()->get()
            ->map(fn (Parcel $p) => ParcelController::card($p));

        $arrivalGuide = $flight->direction === 'arrival'
            ? Guide::published()->where('stage', 'arrival')->orderBy('stage_order')->first(['title', 'slug'])
            : null;

        return Inertia::render('Flights/Show', [
            'flight' => $flight->card(),
            'onBoard' => (bool) $me,
            'myDestination' => $me?->pivot->destination,
            'destinations' => $flight->direction === 'arrival' ? Transit::options() : [],
            // Нэрсийг зөвхөн нэвтэрсэн хэрэглэгчид харуулна (хувийн мэдээлэл).
            'passengers' => $user
                ? $flight->passengers()->orderBy('flight_passengers.created_at')->take(50)->get(['users.id', 'users.name'])
                    ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'destination' => Transit::label($u->pivot->destination)])
                : [],
            'rides' => $rides,
            'parcels' => $parcels,
            'arrivalGuide' => $arrivalGuide,
            'seo' => [
                'title' => $flight->code.' '.$flight->localTime()->format('Y.m.d').' | '.config('app.name'),
                'description' => 'МИАТ-ын '.$flight->code.' нислэг: угтах хүн, хамт явах машин, ачаа.',
                'image' => FlightShareImage::url($flight),
            ],
        ]);
    }

    /** "Би энэ нислэгээр явна" тэмдэглэгээг асаах, унтраах. Ирэх нислэгт цааш хаашаа явахыг хамт хадгална. */
    public function board(Request $request, Flight $flight): RedirectResponse
    {
        abort_if($flight->status === 'cancelled', 422);
        $destination = $this->pickDestination($request, $flight);

        $userId = $request->user()->id;
        if ($flight->passengers()->whereKey($userId)->exists()) {
            $flight->passengers()->detach($userId);

            return back()->with('success', 'Та жагсаалтаас хасагдлаа.');
        }

        $flight->passengers()->attach($userId, ['destination' => $destination]);

        return back()->with('success', 'Та энэ нислэгийн жагсаалтад нэмэгдлээ.');
    }

    /** Жагсаалтад байгаа хүн цааш явах хотоо солих. */
    public function updateDestination(Request $request, Flight $flight): RedirectResponse
    {
        $userId = $request->user()->id;
        abort_unless($flight->passengers()->whereKey($userId)->exists(), 404);

        $flight->passengers()->updateExistingPivot($userId, ['destination' => $this->pickDestination($request, $flight)]);

        return back()->with('success', 'Хадгалагдлаа.');
    }

    /** Хуваалцах зураг (Facebook, Messenger-ийн урьдчилсан харагдац). */
    public function share(Flight $flight): HttpResponse
    {
        return response(FlightShareImage::render($flight), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=900',
        ]);
    }

    private function pickDestination(Request $request, Flight $flight): ?string
    {
        if ($flight->direction !== 'arrival') {
            return null;
        }
        $data = $request->validate(['destination' => ['nullable', 'string', Rule::in(array_keys(Transit::options()))]]);

        return $data['destination'] ?? null;
    }
}
