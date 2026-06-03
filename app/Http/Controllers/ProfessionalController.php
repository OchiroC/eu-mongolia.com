<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesCoverImage;
use App\Models\Professional;
use App\Models\ProfessionalCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProfessionalController extends Controller
{
    use HandlesCoverImage;

    public function index(Request $request): Response
    {
        $pros = Professional::active()
            ->with('category:id,name,slug')
            ->when($request->category, fn ($q, $slug) =>
                $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($request->city, fn ($q, $city) => $q->where('city', 'like', "%{$city}%"))
            ->when($request->lang, fn ($q, $lang) => $q->whereJsonContains('languages', $lang))
            ->when($request->search, fn ($q, $s) =>
                $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")
                    ->orWhere('profession', 'like', "%{$s}%")
                    ->orWhere('services', 'like', "%{$s}%")))
            ->orderedForList()
            ->paginate(12)
            ->withQueryString()
            ->through(fn ($p) => $this->card($p));

        return Inertia::render('Professionals/Index', [
            'professionals' => $pros,
            'categories' => ProfessionalCategory::orderBy('sort_order')->withCount(['professionals' => fn ($q) => $q->where('status', 'active')])->get(['id', 'name', 'slug', 'icon']),
            'languages' => ['Монгол', 'Герман', 'Англи', 'Франц', 'Орос', 'Чех', 'Польш'],
            'filters' => $request->only(['category', 'city', 'lang', 'search']),
            'seo' => [
                'title' => 'Мэргэжилтэн — EU Mongolia',
                'description' => 'Европ дахь монгол мэргэжилтнүүд: хуульч, эмч, орчуулагч, үсчин болон бусад. Баталгаажсан, шууд холбогдоно.',
            ],
        ]);
    }

    public function show(Request $request, Professional $professional): Response
    {
        abort_unless($professional->status === 'active', 404);

        $professional->increment('views');
        $professional->load('category:id,name,slug');

        $related = Professional::active()
            ->where('id', '!=', $professional->id)
            ->where('professional_category_id', $professional->professional_category_id)
            ->orderedForList()
            ->take(4)
            ->get(['id', 'name', 'slug', 'profession', 'photo', 'city', 'is_verified']);

        return Inertia::render('Professionals/Show', [
            'professional' => [
                'id' => $professional->id,
                'name' => $professional->name,
                'slug' => $professional->slug,
                'profession' => $professional->profession,
                'bio' => $professional->bio,
                'photo' => $professional->photo,
                'city' => $professional->city,
                'country' => $professional->country,
                'languages' => $professional->languages ?? [],
                'services' => $professional->services,
                'category' => $professional->category,
                'is_verified' => $professional->is_verified,
                'is_featured' => $professional->isCurrentlyFeatured(),
                'views' => $professional->views,
                'owned' => $request->user()?->id === $professional->user_id,
            ],
            'related' => $related->map(fn ($p) => $this->card($p)),
            'seo' => [
                'title' => $professional->name.($professional->profession ? ' — '.$professional->profession : ''),
                'description' => Str::limit(strip_tags((string) $professional->bio) ?: $professional->profession, 150),
                'image' => $professional->photo,
                'url' => url("/professionals/{$professional->slug}"),
            ],
        ]);
    }

    /** Холбоо барих мэдээллийг зөвхөн нэвтэрсэн хэрэглэгчид нээнэ (+ тоолно). */
    public function reveal(Request $request, Professional $professional): RedirectResponse
    {
        abort_unless($professional->status === 'active', 404);
        $professional->increment('contact_reveals');

        return back()->with('contact', [
            'phone' => $professional->phone,
            'email' => $professional->email,
            'website' => $professional->website,
            'facebook' => $professional->facebook,
        ]);
    }

    public function create(Request $request): Response
    {
        // Аль хэдийн профайлтай бол засах руу нь чиглүүлнэ.
        if ($existing = Professional::where('user_id', $request->user()->id)->first()) {
            return Inertia::render('Professionals/Form', [
                'professional' => $existing,
                'categories' => $this->categories(),
                'languageOptions' => $this->langs(),
            ]);
        }

        return Inertia::render('Professionals/Form', [
            'professional' => null,
            'categories' => $this->categories(),
            'languageOptions' => $this->langs(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Нэг хэрэглэгч нэг профайл.
        if (Professional::where('user_id', $request->user()->id)->exists()) {
            return redirect()->route('professionals.mine');
        }

        $data = $this->validateData($request);
        $data = $this->applyPhoto($request, $data);
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['status'] = 'pending';

        Professional::create($data);

        return redirect()->route('professionals.mine')
            ->with('success', 'Хүсэлт хүлээн авлаа. Админ шалгаж баталгаажуулсны дараа нийтлэгдэнэ.');
    }

    public function mine(Request $request): Response|RedirectResponse
    {
        $professional = Professional::where('user_id', $request->user()->id)->first();

        if (! $professional) {
            return redirect()->route('professionals.create');
        }

        return Inertia::render('Professionals/Mine', [
            'professional' => array_merge($professional->toArray(), [
                'is_currently_featured' => $professional->isCurrentlyFeatured(),
                'featured_until' => $professional->featured_until?->format('Y.m.d'),
            ]),
        ]);
    }

    public function edit(Request $request, Professional $professional): Response
    {
        $this->authorizeOwner($request, $professional);

        return Inertia::render('Professionals/Form', [
            'professional' => $professional,
            'categories' => $this->categories(),
            'languageOptions' => $this->langs(),
        ]);
    }

    public function update(Request $request, Professional $professional): RedirectResponse
    {
        $this->authorizeOwner($request, $professional);

        $data = $this->validateData($request);
        $data = $this->applyPhoto($request, $data, $professional);

        if ($data['name'] !== $professional->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $professional->id);
        }

        // Засвар хийсний дараа дахин хяналтад орно (баталгаажуулалт хадгалагдана).
        $data['status'] = $professional->status === 'inactive' ? 'inactive' : 'pending';

        $professional->update($data);

        return redirect()->route('professionals.mine')->with('success', 'Хадгаллаа. Дахин хяналтад орлоо.');
    }

    /** Mock төлбөр — 30 хоног онцлох болгоно. */
    public function promote(Request $request, Professional $professional): RedirectResponse
    {
        $this->authorizeOwner($request, $professional);

        $professional->update([
            'is_featured' => true,
            'featured_until' => now()->addDays(30),
        ]);

        return back()->with('success', 'Таны профайл 30 хоног онцлох боллоо. (mock төлбөр)');
    }

    /**
     * @return array<string, mixed>
     */
    private function card(Professional $p): array
    {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'profession' => $p->profession,
            'photo' => $p->photo,
            'city' => $p->city,
            'category' => $p->category?->name,
            'is_verified' => $p->is_verified,
            'is_featured' => $p->isCurrentlyFeatured(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyPhoto(Request $request, array $data, ?Professional $existing = null): array
    {
        unset($data['photo']);

        if ($request->hasFile('photo')) {
            $this->deleteCoverUrl($existing?->photo);
            $data['photo'] = $this->storeResizedImage($request->file('photo'), 'professionals', 600);
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
    private function validateData(Request $request): array
    {
        return $request->validate([
            'professional_category_id' => ['nullable', 'exists:professional_categories,id'],
            'name' => ['required', 'string', 'max:120'],
            'profession' => ['nullable', 'string', 'max:150'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:4096'],
            'remove_photo' => ['boolean'],
            'city' => ['nullable', 'string', 'max:80'],
            'country' => ['nullable', 'string', 'max:64'],
            'languages' => ['nullable', 'array', 'max:8'],
            'languages.*' => ['string', 'max:40'],
            'services' => ['nullable', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:120'],
            'website' => ['nullable', 'string', 'max:200'],
            'facebook' => ['nullable', 'string', 'max:200'],
        ]);
    }

    private function categories()
    {
        return ProfessionalCategory::orderBy('sort_order')->get(['id', 'name']);
    }

    /**
     * @return array<int, string>
     */
    private function langs(): array
    {
        return ['Монгол', 'Герман', 'Англи', 'Франц', 'Орос', 'Чех', 'Польш', 'Итали', 'Испани'];
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'pro';
        $slug = $base;
        $i = 1;
        while (Professional::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private function authorizeOwner(Request $request, Professional $professional): void
    {
        abort_unless($professional->user_id === $request->user()->id, 403);
    }
}
