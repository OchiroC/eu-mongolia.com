<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesCoverImage;
use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BusinessController extends Controller
{
    use HandlesCoverImage;

    public function index(Request $request): Response
    {
        $noFilters = ! $request->hasAny(['category', 'city', 'search']);

        $featured = $noFilters
            ? Business::active()
                ->where('is_featured', true)
                ->where(fn ($q) => $q->whereNull('featured_until')->orWhere('featured_until', '>=', now()))
                ->latest()
                ->take(4)
                ->get()
                ->map(fn ($b) => $this->card($b))
            : collect();
        $featuredIds = $featured->pluck('id')->all();

        $businesses = Business::active()
            ->whereNotIn('id', $featuredIds)
            ->when($request->category, fn ($q, $c) => $q->where('category', $c))
            ->when($request->city, fn ($q, $c) => $q->where('city', 'like', "%{$c}%"))
            ->when($request->search, fn ($q, $s) =>
                $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('description', 'like', "%{$s}%")))
            ->orderedForList()
            ->paginate(12)
            ->withQueryString()
            ->through(fn ($b) => $this->card($b));

        $counts = Business::active()->selectRaw('category, count(*) as c')->groupBy('category')->pluck('c', 'category');

        return Inertia::render('Businesses/Index', [
            'businesses' => $businesses,
            'featured' => $featured,
            'categories' => collect(Business::CATEGORIES)->map(fn ($label, $key) => [
                'key' => $key, 'label' => $label, 'count' => (int) ($counts[$key] ?? 0),
            ])->values(),
            'cities' => Business::active()->distinct()->orderBy('city')->pluck('city'),
            'filters' => $request->only(['category', 'city', 'search']),
            'seo' => [
                'title' => 'Монгол бизнес лавлах — EU Mongolia',
                'description' => 'Европ дахь монгол ресторан, дэлгүүр, бизнесүүдийн лавлах. Хаана юу байгааг олоорой.',
            ],
        ]);
    }

    public function show(Request $request, Business $business): Response
    {
        abort_unless($business->status === 'active' || $business->user_id === $request->user()?->id, 404);

        $business->increment('views');

        $address = trim(implode(', ', array_filter([$business->address, $business->city, $business->country])));

        return Inertia::render('Businesses/Show', [
            'business' => [
                'id' => $business->id,
                'name' => $business->name,
                'slug' => $business->slug,
                'category_label' => $business->category_label,
                'description' => $business->description,
                'city' => $business->city,
                'country' => $business->country,
                'address' => $business->address,
                'phone' => $business->phone,
                'email' => $business->email,
                'website' => $business->website,
                'facebook' => $business->facebook,
                'hours' => $business->hours,
                'photo' => $business->photo,
                'is_featured' => $business->isCurrentlyFeatured(),
                'status' => $business->status,
                'views' => $business->views,
                'owned' => $request->user()?->id === $business->user_id,
                'map_query' => $address !== '' ? $address : null,
            ],
            'similar' => Business::active()
                ->where('id', '!=', $business->id)
                ->where('category', $business->category)
                ->orderedForList()
                ->take(4)
                ->get()
                ->map(fn ($b) => $this->card($b)),
            'seo' => [
                'title' => $business->name,
                'description' => Str::limit(strip_tags((string) $business->description), 150) ?: $business->category_label,
                'image' => $business->photo,
                'url' => url("/businesses/{$business->slug}"),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Businesses/Form', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data = $this->applyPhoto($request, $data);
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['status'] = 'pending';

        Business::create($data);

        return redirect()->route('businesses.my')->with('success', 'Хүсэлт хүлээн авлаа. Админ шалгаж нийтэлнэ.');
    }

    public function myBusinesses(Request $request): Response
    {
        return Inertia::render('Businesses/My', [
            'businesses' => Business::where('user_id', $request->user()->id)
                ->latest()
                ->get()
                ->map(fn ($b) => array_merge($this->card($b), [
                    'status' => $b->status,
                    'views' => $b->views,
                    'is_featured' => $b->isCurrentlyFeatured(),
                    'featured_until' => $b->isCurrentlyFeatured() ? $b->featured_until?->format('Y.m.d') : null,
                ])),
        ]);
    }

    public function edit(Request $request, Business $business): Response
    {
        $this->authorizeOwner($request, $business);

        return Inertia::render('Businesses/Form', array_merge($this->formOptions(), ['business' => $business]));
    }

    public function update(Request $request, Business $business): RedirectResponse
    {
        $this->authorizeOwner($request, $business);

        $data = $this->validateData($request);
        $data = $this->applyPhoto($request, $data, $business);
        if ($data['name'] !== $business->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $business->id);
        }
        $business->update($data);

        return redirect()->route('businesses.my')->with('success', 'Шинэчлэгдлээ.');
    }

    public function destroy(Request $request, Business $business): RedirectResponse
    {
        $this->authorizeOwner($request, $business);
        $this->deleteCoverUrl($business->photo);
        $business->delete();

        return back()->with('success', 'Устгагдлаа.');
    }

    /** Mock төлбөр — 30 хоног онцлох. */
    public function promote(Request $request, Business $business): RedirectResponse
    {
        $this->authorizeOwner($request, $business);

        $base = $business->isCurrentlyFeatured() && $business->featured_until ? $business->featured_until : now();
        $business->update(['is_featured' => true, 'featured_until' => $base->copy()->addDays(30)]);

        return back()->with('success', 'Бизнес 30 хоног онцлох боллоо. (mock төлбөр)');
    }

    /**
     * @return array<string, mixed>
     */
    private function card(Business $b): array
    {
        return [
            'id' => $b->id,
            'name' => $b->name,
            'slug' => $b->slug,
            'category_label' => $b->category_label,
            'city' => $b->city,
            'country' => $b->country,
            'photo' => $b->photo,
            'is_featured' => $b->isCurrentlyFeatured(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyPhoto(Request $request, array $data, ?Business $existing = null): array
    {
        unset($data['photo']);
        if ($request->hasFile('photo')) {
            $this->deleteCoverUrl($existing?->photo);
            $data['photo'] = $this->storeResizedImage($request->file('photo'), 'businesses');
        } elseif ($request->boolean('remove_photo')) {
            $this->deleteCoverUrl($existing?->photo);
            $data['photo'] = null;
        }
        unset($data['remove_photo']);

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'categories' => collect(Business::CATEGORIES)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
            'countries' => ['Герман', 'Чех', 'Польш', 'Унгар', 'Австри', 'Франц', 'Бельги', 'Голланд', 'Швед', 'Итали', 'Испани'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'category' => ['required', Rule::in(array_keys(Business::CATEGORIES))],
            'description' => ['nullable', 'string', 'max:3000'],
            'city' => ['required', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:64'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:160'],
            'website' => ['nullable', 'string', 'max:200'],
            'facebook' => ['nullable', 'string', 'max:200'],
            'hours' => ['nullable', 'string', 'max:160'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:4096'],
            'remove_photo' => ['boolean'],
        ]);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'business';
        $slug = $base;
        $i = 1;
        while (Business::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private function authorizeOwner(Request $request, Business $business): void
    {
        abort_unless($business->user_id === $request->user()->id, 403);
    }
}
