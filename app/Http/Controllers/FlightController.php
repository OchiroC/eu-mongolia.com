<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use App\Models\Guide;
use App\Models\Parcel;
use App\Models\Ride;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'onBoard' => $user ? $flight->passengers()->whereKey($user->id)->exists() : false,
            // Нэрсийг зөвхөн нэвтэрсэн хэрэглэгчид харуулна (хувийн мэдээлэл).
            'passengers' => $user
                ? $flight->passengers()->orderBy('flight_passengers.created_at')->take(50)->get(['users.id', 'users.name'])
                    ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])
                : [],
            'rides' => $rides,
            'parcels' => $parcels,
            'arrivalGuide' => $arrivalGuide,
            'seo' => [
                'title' => $flight->code.' '.$flight->localTime()->format('Y.m.d').' | '.config('app.name'),
                'description' => 'МИАТ-ын '.$flight->code.' нислэг: угтах хүн, хамт явах машин, ачаа.',
            ],
        ]);
    }

    /** "Би энэ нислэгээр явна" тэмдэглэгээг асаах, унтраах. */
    public function board(Request $request, Flight $flight): RedirectResponse
    {
        abort_if($flight->status === 'cancelled', 422);

        $result = $flight->passengers()->toggle($request->user()->id);
        $joined = count($result['attached']) > 0;

        return back()->with('success', $joined ? 'Та энэ нислэгийн жагсаалтад нэмэгдлээ.' : 'Та жагсаалтаас хасагдлаа.');
    }
}
