<?php

namespace App\Http\Controllers;

use App\Models\KidsResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KidsController extends Controller
{
    public function index(Request $request): Response
    {
        $resources = KidsResource::active()
            ->when($request->category, fn ($q, $c) => $q->where('category', $c))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->latest()
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'title' => $r->title,
                'category' => $r->category,
                'category_label' => $r->category_label,
                'description' => $r->description,
                'url' => $r->url,
                'city' => $r->city,
                'country' => $r->country,
                'contact' => $r->contact,
                'age_range' => $r->age_range,
                'is_featured' => $r->is_featured,
            ]);

        $counts = KidsResource::active()->selectRaw('category, count(*) as c')->groupBy('category')->pluck('c', 'category');

        return Inertia::render('Kids/Index', [
            'resources' => $resources,
            'categories' => collect(KidsResource::CATEGORIES)->map(fn ($label, $key) => [
                'key' => $key, 'label' => $label, 'count' => (int) ($counts[$key] ?? 0),
            ])->values(),
            'filters' => $request->only(['category']),
            'seo' => [
                'title' => 'Хүүхдийн булан — монгол хэл, соёл — Yazguur',
                'description' => 'Гадаадад өссөн монгол хүүхдүүдэд зориулсан монгол хэл сурах, соёл уламжлал, сургууль бүлгэмийн нөөц.',
            ],
        ]);
    }
}
