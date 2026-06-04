<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ride;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RideController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Rides/Index', [
            'rides' => Ride::with('user:id,name')
                ->orderByDesc('depart_at')
                ->paginate(20)
                ->through(fn ($r) => [
                    'id' => $r->id,
                    'route' => $r->from_city.' → '.$r->to_city,
                    'depart_at' => $r->depart_at?->format('Y.m.d H:i'),
                    'seats' => $r->seats,
                    'user' => $r->user?->name,
                    'status' => $r->status,
                ]),
        ]);
    }

    public function close(Ride $ride): RedirectResponse
    {
        $ride->update(['status' => $ride->status === 'closed' ? 'active' : 'closed']);

        return back()->with('success', 'Төлөв шинэчлэгдлээ.');
    }

    public function destroy(Ride $ride): RedirectResponse
    {
        $ride->delete();

        return back()->with('success', 'Устгагдлаа.');
    }
}
