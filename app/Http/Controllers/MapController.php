<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Professional;
use Inertia\Inertia;
use Inertia\Response;

class MapController extends Controller
{
    /** Байршил оруулсан монгол бизнес, мэргэжилтнүүдийн газрын зураг. */
    public function index(): Response
    {
        $businesses = Business::active()->whereNotNull('lat')->whereNotNull('lng')->get()
            ->map(fn ($b) => [
                'kind' => 'business',
                'name' => $b->name,
                'meta' => $b->category_label,
                'city' => $b->city,
                'address' => $b->address,
                'href' => "/businesses/{$b->slug}",
                'lat' => (float) $b->lat,
                'lng' => (float) $b->lng,
            ]);

        $professionals = Professional::active()->whereNotNull('lat')->whereNotNull('lng')->get()
            ->map(fn ($p) => [
                'kind' => 'professional',
                'name' => $p->name,
                'meta' => $p->profession,
                'city' => $p->city,
                'address' => null,
                'href' => "/professionals/{$p->slug}",
                'lat' => (float) $p->lat,
                'lng' => (float) $p->lng,
            ]);

        return Inertia::render('Map/Index', [
            'places' => $businesses->concat($professionals)->values(),
            'seo' => [
                'title' => 'Газрын зураг | '.config('app.name'),
                'description' => 'Франкфурт болон ойр орчмын монгол бизнес, монголоор үйлчилдэг мэргэжилтнүүдийн байршил.',
            ],
        ]);
    }
}
