<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Flight;
use App\Models\FlightSchedule;
use App\Support\FlightGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FlightController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Flights/Index', [
            'schedules' => FlightSchedule::orderBy('direction')->orderBy('code')->get()
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'code' => $s->code,
                    'direction' => $s->direction,
                    'origin' => $s->origin,
                    'destination' => $s->destination,
                    'weekdays' => array_map('intval', $s->weekdays),
                    'local_time' => substr((string) $s->local_time, 0, 5),
                    'valid_from' => $s->valid_from->format('Y-m-d'),
                    'valid_to' => $s->valid_to?->format('Y-m-d'),
                ]),
            'flights' => Flight::upcoming()->withCount('passengers')->orderBy('scheduled_at')->take(30)->get()
                ->map(fn ($f) => $f->card() + ['local' => $f->localTime()->format('Y-m-d\TH:i')]),
            'weekdays' => FlightSchedule::WEEKDAYS,
            'statuses' => Flight::STATUSES,
        ]);
    }

    public function storeSchedule(Request $request, FlightGenerator $generator): RedirectResponse
    {
        $schedule = FlightSchedule::create($this->validateSchedule($request));
        $generator->forSchedule($schedule);

        return back()->with('success', 'Хуваарь нэмэгдэж, нислэгүүд үүслээ.');
    }

    public function updateSchedule(Request $request, FlightSchedule $schedule, FlightGenerator $generator): RedirectResponse
    {
        $schedule->update($this->validateSchedule($request));

        // Хуваарьт тохирохгүй болсон, хэн ч холбогдоогүй ирээдүйн нислэгийг цэвэрлэнэ.
        $schedule->flights()->where('scheduled_at', '>', now())->where('status', 'scheduled')
            ->doesntHave('passengers')->doesntHave('rides')->doesntHave('parcels')->delete();
        $generator->forSchedule($schedule);

        return back()->with('success', 'Хуваарь шинэчлэгдлээ.');
    }

    public function destroySchedule(FlightSchedule $schedule): RedirectResponse
    {
        $schedule->flights()->where('scheduled_at', '>', now())
            ->doesntHave('passengers')->doesntHave('rides')->doesntHave('parcels')->delete();
        $schedule->delete();

        return back()->with('success', 'Хуваарь устгагдлаа.');
    }

    /** Нэг нислэгийн төлөв, цаг, тайлбарыг засна (хойшилсон, цуцлагдсан). */
    public function updateFlight(Request $request, Flight $flight): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Flight::STATUSES))],
            'local' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $flight->update([
            'status' => $data['status'],
            'scheduled_at' => \Illuminate\Support\Carbon::parse($data['local'], Flight::TZ)->utc(),
            'note' => $data['note'] ?? null,
        ]);

        return back()->with('success', 'Нислэг шинэчлэгдлээ.');
    }

    public function generate(FlightGenerator $generator): RedirectResponse
    {
        $count = $generator->generate();

        return back()->with('success', "Шинээр {$count} нислэг үүслээ.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validateSchedule(Request $request): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:10', 'regex:/^[A-Za-z0-9]+$/'],
            'direction' => ['required', Rule::in(['arrival', 'departure'])],
            'origin' => ['required', 'string', 'size:3'],
            'destination' => ['required', 'string', 'size:3'],
            'weekdays' => ['required', 'array', 'min:1'],
            'weekdays.*' => ['integer', 'between:1,7'],
            'local_time' => ['required', 'date_format:H:i'],
            'valid_from' => ['required', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
        ]);

        $data['code'] = strtoupper($data['code']);
        $data['origin'] = strtoupper($data['origin']);
        $data['destination'] = strtoupper($data['destination']);
        $data['weekdays'] = array_values(array_unique(array_map('intval', $data['weekdays'])));
        sort($data['weekdays']);

        return $data;
    }
}
