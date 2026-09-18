<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parcel;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ParcelController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Parcels/Index', [
            'parcels' => Parcel::with(['user:id,name', 'flight'])
                ->latest()
                ->paginate(20)
                ->through(fn ($p) => [
                    'id' => $p->id,
                    'type' => Parcel::TYPES[$p->type] ?? $p->type,
                    'route' => $p->from_city.' → '.$p->to_city,
                    'date' => $p->flight ? $p->flight->code.' · '.$p->flight->localTime()->format('Y.m.d') : $p->travel_date?->format('Y.m.d'),
                    'user' => $p->user?->name,
                    'status' => $p->status,
                ]),
        ]);
    }

    public function close(Parcel $parcel): RedirectResponse
    {
        $parcel->update(['status' => $parcel->status === 'closed' ? 'active' : 'closed']);

        return back()->with('success', 'Төлөв шинэчлэгдлээ.');
    }

    public function destroy(Parcel $parcel): RedirectResponse
    {
        $parcel->delete();

        return back()->with('success', 'Устгагдлаа.');
    }
}
