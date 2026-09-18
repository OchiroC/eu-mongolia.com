<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Flight;
use App\Models\Guide;
use App\Models\HousingPost;
use App\Models\JobPost;
use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\Post;
use App\Models\Professional;
use App\Models\Ride;
use App\Support\Transit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            // Зар (гол хэсэг)
            'categories' => ListingCategory::orderBy('sort_order')
                ->withCount(['listings' => fn ($q) => $q->where('status', 'active')])
                ->get(['id', 'name', 'slug', 'icon']),
            'featuredListings' => Listing::active()
                ->currentlyFeatured()
                ->with('category:id,name,slug,icon')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($l) => $this->card($l)),
            'latestListings' => Listing::active()
                ->whereNot(fn ($q) => $q->currentlyFeatured())
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
            // Онцлох (төлбөртэй) мэргэжлийн үйлчилгээ — нүүрэнд зөвхөн онцлохыг харуулна.
            'featuredProfessionals' => Professional::active()
                ->where('is_featured', true)
                ->where(fn ($q) => $q->whereNull('featured_until')->orWhere('featured_until', '>=', now()))
                ->with('category:id,name')
                ->latest()
                ->take(8)
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
            // Чиглүүлэх самбарын мөр бүрийн тоо — самбар хэзээ ч хоосон харагдахгүй.
            'counts' => [
                'flights' => Flight::upcoming()->where('direction', 'arrival')->where('scheduled_at', '<=', now()->addDays(14))->count(),
                'rides' => Ride::active()->upcoming()->count(),
                'guides' => Guide::published()->count(),
                'transit' => count(Transit::CITIES),
                'housing' => HousingPost::active()->count(),
                'jobs' => JobPost::active()->count(),
                'listings' => Listing::active()->count(),
                'events' => Event::published()->where('starts_at', '>=', now())->count(),
            ],
            // Явах самбар: удахгүй болох аяллууд.
            'upcomingRides' => Ride::active()
                ->upcoming()
                ->orderBy('depart_at')
                ->take(6)
                ->get(['id', 'from_city', 'to_city', 'depart_at', 'seats', 'price']),
            // Аяллын зам: үе шат бүрийн гарын авлага дарааллаараа.
            'journey' => $this->journey(),
            // Нэвтэрсэн хэрэглэгчийн хийсэн гэж тэмдэглэсэн алхмууд (зочинд localStorage ашиглана).
            'journeyDone' => $request->user()?->journeyGuides()->pluck('slug') ?? [],
            // Нислэгийн самбар: ойрын ирэх, явах нислэгүүд.
            'upcomingFlights' => Flight::upcoming()
                ->withCount(['passengers', 'rides' => fn ($q) => $q->where('status', 'active'), 'parcels' => fn ($q) => $q->where('status', 'active')])
                ->orderBy('scheduled_at')
                ->take(8)
                ->get()
                ->map->card(),
            // Мэдэгдлийн мөрөнд харагдах гарын авлагууд.
            'guides' => Guide::published()
                ->orderByDesc('is_featured')
                ->latest('published_at')
                ->take(6)
                ->get(['id', 'title', 'slug', 'excerpt', 'topic'])
                ->map(fn ($g) => [
                    'title' => $g->title,
                    'slug' => $g->slug,
                    'excerpt' => $g->excerpt,
                    'topic' => $g->topic_label,
                ]),
            'seo' => [
                'title' => config('app.name').' | Франкфурт дахь монголчуудын мэдээллийн сайт',
                'description' => 'Франкфурт орчимд амьдардаг болон Франкфуртаар дамжин ирж буй монголчуудад зориулсан байр, ажил, зар, арга хэмжээ, бичиг баримтын заавар.',
            ],
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function journey(): array
    {
        $byStage = Guide::published()
            ->whereNotNull('stage')
            ->orderBy('stage_order')
            ->orderBy('id')
            ->get(['id', 'title', 'slug', 'excerpt', 'stage'])
            ->groupBy('stage');

        return collect(Guide::STAGES)
            ->map(fn ($label, $key) => [
                'key' => $key,
                'label' => $label,
                'guides' => ($byStage[$key] ?? collect())
                    ->map(fn ($g) => ['title' => $g->title, 'slug' => $g->slug, 'excerpt' => $g->excerpt])
                    ->values(),
            ])
            ->values()
            ->all();
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
            'is_featured' => $l->isCurrentlyFeatured(),
            'created_at' => $l->created_at->toIso8601String(),
        ];
    }
}
