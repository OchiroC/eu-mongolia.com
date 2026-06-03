<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\Post;
use App\Models\Professional;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            // Зар (гол хэсэг)
            'categories' => ListingCategory::orderBy('sort_order')
                ->withCount(['listings' => fn ($q) => $q->where('status', 'active')])
                ->get(['id', 'name', 'slug', 'icon']),
            'featuredListings' => Listing::active()
                ->where('is_featured', true)
                ->with('category:id,name,slug,icon')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($l) => $this->card($l)),
            'latestListings' => Listing::active()
                ->where('is_featured', false)
                ->with('category:id,name,slug,icon')
                ->latest()
                ->take(8)
                ->get()
                ->map(fn ($l) => $this->card($l)),
            // Мэдээ — онцлохыг тэргүүлүүлж харуулна.
            'featuredNews' => Post::published()
                ->orderByDesc('is_featured')
                ->latest('published_at')
                ->take(3)
                ->get(['id', 'title', 'slug', 'excerpt', 'cover_image', 'is_featured', 'published_at']),
            // Онцлох эвент — нүүрэнд дээр том харагдана.
            'featuredEvents' => Event::published()
                ->where('is_featured', true)
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->take(3)
                ->get(['id', 'title', 'slug', 'description', 'cover_image', 'venue', 'city', 'starts_at']),
            // Удахгүй болох (онцлохоос бусад) эвентүүд.
            'upcomingEvents' => Event::published()
                ->where('is_featured', false)
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->take(3)
                ->get(['id', 'title', 'slug', 'cover_image', 'venue', 'city', 'starts_at']),
            // Онцлох мэргэжилтэн (лавлахын реклам)
            'featuredProfessionals' => Professional::active()
                ->with('category:id,name')
                ->orderedForList()
                ->take(6)
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'profession' => $p->profession,
                    'photo' => $p->photo,
                    'city' => $p->city,
                    'category' => $p->category?->name,
                    'is_verified' => $p->is_verified,
                    'is_featured' => $p->isCurrentlyFeatured(),
                ]),
            'stats' => [
                'listings' => Listing::where('status', 'active')->count(),
                'news' => Post::where('status', 'published')->count(),
                'events' => Event::where('status', 'published')->count(),
            ],
            'seo' => [
                'title' => 'EU Mongolia — Европ дахь монголчуудын платформ',
                'description' => 'Зар, мэдээ, эвент — бүгд нэг дороос. Худалдаа, ажил, орон сууц, үйлчилгээ.',
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function card(Listing $l): array
    {
        return [
            'id' => $l->id,
            'title' => $l->title,
            'slug' => $l->slug,
            'price' => $l->price,
            'price_type' => $l->price_type,
            'city' => $l->city,
            'postal_code' => $l->postal_code,
            'cover' => $l->cover,
            'is_featured' => $l->is_featured,
            'created_at' => $l->created_at->toIso8601String(),
        ];
    }
}
