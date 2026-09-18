<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use App\Models\Ride;
use App\Support\Transit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/** Франкфуртаар дамжин Европын бусад хот руу явах хүмүүст. */
class TransitController extends Controller
{
    public function index(): Response
    {
        // Ойрын ирэх нислэгүүдээр хот бүр рүү хэдэн хүн явахыг тоолно.
        $counts = DB::table('flight_passengers')
            ->join('flights', 'flights.id', '=', 'flight_passengers.flight_id')
            ->where('flights.direction', 'arrival')
            ->where('flights.scheduled_at', '>=', now()->subHours(6))
            ->whereNotNull('flight_passengers.destination')
            ->groupBy('flight_passengers.destination')
            ->pluck(DB::raw('count(*)'), 'flight_passengers.destination');

        $cities = collect(Transit::all())->map(fn ($c) => $c + ['travellers' => (int) ($counts[$c['slug']] ?? 0)]);

        return Inertia::render('Transit/Index', [
            'cities' => $cities,
            'seo' => [
                'title' => 'Франкфуртаар дамжих | '.config('app.name'),
                'description' => 'Франкфуртын нисэх буудлаар дамжин Европын бусад хот руу явах монголчуудад: виз, терминал солих, галт тэрэг, ачаа, хамт явах хүн.',
            ],
        ]);
    }

    public function show(Request $request, string $city): Response
    {
        abort_unless(Transit::exists($city), 404);
        $card = Transit::card($city);
        $user = $request->user();

        $flights = Flight::upcoming()
            ->where('direction', 'arrival')
            ->where('status', '!=', 'cancelled')
            ->orderBy('scheduled_at')
            ->take(8)
            ->withCount(['passengers as travellers_count' => fn ($q) => $q->where('flight_passengers.destination', $city)])
            ->get()
            ->map(function (Flight $f) use ($city, $user) {
                $row = $f->card() + ['travellers' => $f->travellers_count];
                // Нэрсийг зөвхөн нэвтэрсэн хэрэглэгчид харуулна.
                $row['names'] = $user && $f->travellers_count
                    ? $f->passengers()->wherePivot('destination', $city)->take(12)->pluck('users.name')
                    : [];

                return $row;
            });

        // Хот руу явах машин: хэрэглэгч хотын нэрийг монголоор эсвэл тухайн орны хэлээр бичсэн байж болно.
        $rides = Ride::active()->upcoming()
            ->where(fn ($q) => $q->where('to_city', 'like', '%'.$card['name'].'%')->orWhere('to_city', 'like', '%'.$card['local'].'%'))
            ->with('user:id,name')
            ->orderBy('depart_at')
            ->take(6)
            ->get()
            ->map(fn (Ride $r) => [
                'id' => $r->id,
                'from_city' => $r->from_city,
                'to_city' => $r->to_city,
                'depart_at' => $r->depart_at?->toIso8601String(),
                'seats' => $r->seats,
                'user' => $r->user?->name ?? 'Хэрэглэгч',
            ]);

        return Inertia::render('Transit/Show', [
            'city' => $card,
            'flights' => $flights,
            'rides' => $rides,
            'seo' => [
                'title' => 'Франкфуртаас '.$card['to'].' | '.config('app.name'),
                'description' => 'Франкфуртын нисэх буудлаас '.$card['name'].' хүртэл: '.mb_strtolower($card['mode_label']).', '.$card['time'].'. Виз, ачаа, хамт явах хүн.',
            ],
        ]);
    }
}
