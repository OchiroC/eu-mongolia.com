<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesCoverImage;
use App\Models\Listing;
use App\Models\ListingCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ListingController extends Controller
{
    use HandlesCoverImage;

    /**
     * Зарын жагсаалт — ангилал, хайлт, байршил, үнээр шүүнэ.
     */
    public function index(Request $request): Response
    {
        $listings = Listing::active()
            ->with(['category:id,name,slug,icon'])
            ->when($request->category, fn ($q, $slug) =>
                $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($request->search, fn ($q, $s) =>
                $q->where(fn ($w) => $w->where('title', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%")))
            ->when($request->location, fn ($q, $loc) =>
                $q->where(fn ($w) => $w->where('city', 'like', "%{$loc}%")
                    ->orWhere('postal_code', 'like', "%{$loc}%")))
            ->when($request->min_price, fn ($q, $v) => $q->where('price', '>=', $v))
            ->when($request->max_price, fn ($q, $v) => $q->where('price', '<=', $v))
            ->when($request->price_type === 'free', fn ($q) => $q->whereIn('price_type', ['free', 'giveaway']))
            ->orderedForList()
            ->paginate(16)
            ->withQueryString()
            ->through(fn ($l) => $this->cardData($l));

        return Inertia::render('Zar/Index', [
            'listings' => $listings,
            'categories' => $this->categoriesWithCounts(),
            'filters' => $request->only(['category', 'search', 'location', 'min_price', 'max_price', 'price_type']),
        ]);
    }

    /**
     * Зарын дэлгэрэнгүй.
     */
    public function show(Request $request, Listing $listing): Response
    {
        abort_unless($listing->status !== 'inactive', 404);

        $listing->increment('views');
        $listing->load(['category:id,name,slug', 'user:id,name,created_at']);

        $similar = Listing::active()
            ->where('id', '!=', $listing->id)
            ->where('listing_category_id', $listing->listing_category_id)
            ->latest()
            ->take(4)
            ->get()
            ->map(fn ($l) => $this->cardData($l));

        return Inertia::render('Zar/Show', [
            'listing' => [
                'id' => $listing->id,
                'title' => $listing->title,
                'description' => $listing->description,
                'price' => $listing->price,
                'price_type' => $listing->price_type,
                'condition' => $listing->condition,
                'postal_code' => $listing->postal_code,
                'city' => $listing->city,
                'country' => $listing->country,
                'images' => $listing->images ?? [],
                'status' => $listing->status,
                'views' => $listing->views,
                'created_at' => $listing->created_at->toIso8601String(),
                'category' => $listing->category,
                'contact_name' => $listing->contact_name,
                'contact_phone' => $listing->contact_phone,
                'contact_email' => $listing->contact_email,
                'seller' => [
                    'name' => $listing->user->name,
                    'since' => $listing->user->created_at->translatedFormat('Y оны M'),
                ],
                'owned' => $request->user()?->id === $listing->user_id,
            ],
            'similar' => $similar,
            'seo' => [
                'title' => $listing->title,
                'description' => Str::limit(strip_tags($listing->description), 150),
                'image' => $listing->images[0] ?? null,
                'url' => url("/zar/{$listing->slug}"),
                'jsonld' => array_filter([
                    '@context' => 'https://schema.org',
                    '@type' => 'Product',
                    'name' => $listing->title,
                    'description' => Str::limit(strip_tags($listing->description), 250),
                    'image' => $listing->images ?? [],
                    'offers' => ($listing->price_type === 'fixed' && $listing->price > 0) ? [
                        '@type' => 'Offer',
                        'price' => (string) $listing->price,
                        'priceCurrency' => 'EUR',
                        'availability' => $listing->status === 'active' ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                        'url' => url("/zar/{$listing->slug}"),
                    ] : null,
                ]),
            ],
        ]);
    }

    /**
     * Хэрэглэгчийн өөрийн зарууд.
     */
    public function myListings(Request $request): Response
    {
        $listings = Listing::where('user_id', $request->user()->id)
            ->with('category:id,name')
            ->latest()
            ->get()
            ->map(fn ($l) => [
                ...$this->cardData($l),
                'status' => $l->status,
                'views' => $l->views,
                'is_featured' => $l->isCurrentlyFeatured(),
                'featured_until' => $l->isCurrentlyFeatured() ? $l->featured_until?->format('Y.m.d') : null,
            ]);

        return Inertia::render('Zar/MyListings', ['listings' => $listings]);
    }

    public function create(): Response
    {
        return Inertia::render('Zar/Create', [
            'categories' => ListingCategory::orderBy('sort_order')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data = $this->resolveImages($request, $data);
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['title']);

        Listing::create($data);

        return redirect()->route('zar.my')->with('success', 'Зар нийтлэгдлээ.');
    }

    public function edit(Request $request, Listing $listing): Response
    {
        $this->authorizeOwner($request, $listing);

        return Inertia::render('Zar/Edit', [
            'listing' => $listing,
            'categories' => ListingCategory::orderBy('sort_order')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Listing $listing): RedirectResponse
    {
        $this->authorizeOwner($request, $listing);

        $data = $this->validateData($request);
        $data = $this->resolveImages($request, $data, $listing);
        if ($data['title'] !== $listing->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $listing->id);
        }

        $listing->update($data);

        return redirect()->route('zar.my')->with('success', 'Зар шинэчлэгдлээ.');
    }

    public function destroy(Request $request, Listing $listing): RedirectResponse
    {
        $this->authorizeOwner($request, $listing);
        foreach ($listing->images ?? [] as $url) {
            $this->deleteCoverUrl($url);
        }
        $listing->delete();

        return redirect()->route('zar.my')->with('success', 'Зар устгагдлаа.');
    }

    /**
     * Зарагдсан гэж тэмдэглэх / эргүүлэн идэвхжүүлэх.
     */
    public function markStatus(Request $request, Listing $listing): RedirectResponse
    {
        $this->authorizeOwner($request, $listing);
        $data = $request->validate(['status' => ['required', 'in:active,sold,inactive']]);
        $listing->update($data);

        return back()->with('success', 'Төлөв шинэчлэгдлээ.');
    }

    /** Mock төлбөр — зарыг тодорхой хоногоор онцлох болгоно (хугацаа дуусаагүй бол сунгана). */
    public function promote(Request $request, Listing $listing): RedirectResponse
    {
        $this->authorizeOwner($request, $listing);

        $days = (int) $request->validate([
            'days' => ['required', 'integer', 'in:7,14,30'],
        ])['days'];

        $base = $listing->isCurrentlyFeatured() && $listing->featured_until
            ? $listing->featured_until
            : now();

        $listing->update([
            'is_featured' => true,
            'featured_until' => $base->copy()->addDays($days),
        ]);

        return back()->with('success', "Таны зар {$days} хоног онцлох боллоо. (mock төлбөр)");
    }

    /**
     * @return array<string, mixed>
     */
    private function cardData(Listing $l): array
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
            'category' => $l->category?->only(['name', 'slug', 'icon']),
            'created_at' => $l->created_at->toIso8601String(),
        ];
    }

    private function categoriesWithCounts()
    {
        return ListingCategory::orderBy('sort_order')
            ->withCount(['listings' => fn ($q) => $q->where('status', 'active')])
            ->get(['id', 'name', 'slug', 'icon']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'listing_category_id' => ['required', 'exists:listing_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'price_type' => ['required', 'in:fixed,negotiable,free,giveaway'],
            'condition' => ['nullable', 'in:new,used'],
            'postal_code' => ['nullable', 'string', 'max:12'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:64'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'image_order' => ['nullable', 'array', 'max:8'],
            'image_order.*' => ['string', 'max:1024'],
            'new_images' => ['nullable', 'array', 'max:8'],
            'new_images.*' => ['image', 'mimes:jpeg,png,webp', 'max:4096'],
            'status' => ['nullable', 'in:active,sold,inactive'],
        ]);
    }

    /**
     * Зургийн эрэмбэ (image_order) болон шинэ upload (new_images)-аас эцсийн
     * images массивыг бүрдүүлнэ. Хасагдсан өөрийн storage зургийг устгана.
     * Токен: "new:N" → new_images[N], бусад нь хуучин URL. (Хамгийн ихдээ 8.)
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function resolveImages(Request $request, array $data, ?Listing $existing = null): array
    {
        $order = $data['image_order'] ?? [];
        unset($data['image_order'], $data['new_images']);

        $stored = [];
        foreach ($request->file('new_images', []) as $i => $file) {
            $stored[$i] = $this->storeResizedImage($file, 'listings');
        }

        $final = [];
        foreach ($order as $token) {
            if (Str::startsWith($token, 'new:')) {
                $idx = (int) Str::after($token, 'new:');
                if (isset($stored[$idx])) {
                    $final[] = $stored[$idx];
                }
            } else {
                $final[] = $token;
            }
        }
        $final = array_slice($final, 0, 8);

        if ($existing) {
            foreach (array_diff($existing->images ?? [], $final) as $removed) {
                $this->deleteCoverUrl($removed);
            }
        }

        $data['images'] = $final;

        return $data;
    }

    private function authorizeOwner(Request $request, Listing $listing): void
    {
        abort_unless($listing->user_id === $request->user()->id, 403);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'zar';
        $slug = $base;
        $i = 1;
        while (Listing::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
