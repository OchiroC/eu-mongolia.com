<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesCoverImage;
use App\Models\HousingPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class HousingController extends Controller
{
    use HandlesCoverImage;

    public function index(Request $request): Response
    {
        $posts = HousingPost::active()
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->when($request->city, fn ($q, $c) => $q->where('city', 'like', "%{$c}%"))
            ->when($request->min_price, fn ($q, $v) => $q->where('price', '>=', $v))
            ->when($request->max_price, fn ($q, $v) => $q->where('price', '<=', $v))
            ->when($request->boolean('furnished'), fn ($q) => $q->where('furnished', true))
            ->latest()
            ->paginate(12)
            ->withQueryString()
            ->through(fn ($p) => $this->card($p));

        $counts = HousingPost::active()->selectRaw('type, count(*) as c')->groupBy('type')->pluck('c', 'type');

        return Inertia::render('Housing/Index', [
            'posts' => $posts,
            'types' => collect(HousingPost::TYPES)->map(fn ($label, $key) => [
                'key' => $key, 'label' => $label, 'count' => (int) ($counts[$key] ?? 0),
            ])->values(),
            'filters' => $request->only(['type', 'city', 'min_price', 'max_price', 'furnished']),
            'seo' => [
                'title' => 'Орон сууц / Өрөө хуваалцах — Yazguur',
                'description' => 'Европ дахь монголчуудад зориулсан орон сууц, өрөө түрээс, WG хуваалцах зар.',
            ],
        ]);
    }

    public function show(Request $request, HousingPost $housing): Response
    {
        abort_unless($housing->status === 'active' || $housing->user_id === $request->user()?->id, 404);

        $housing->increment('views');
        $housing->load('user:id,name');

        return Inertia::render('Housing/Show', [
            'post' => [
                'id' => $housing->id,
                'title' => $housing->title,
                'slug' => $housing->slug,
                'type_label' => $housing->type_label,
                'city' => $housing->city,
                'country' => $housing->country,
                'district' => $housing->district,
                'price' => $housing->price,
                'deposit' => $housing->deposit,
                'rooms' => $housing->rooms,
                'size' => $housing->size,
                'available_from' => $housing->available_from?->format('Y.m.d'),
                'furnished' => $housing->furnished,
                'gender_pref' => HousingPost::GENDERS[$housing->gender_pref] ?? null,
                'description' => $housing->description,
                'images' => $housing->images ?? [],
                'status' => $housing->status,
                'user' => $housing->user?->name ?? 'Хэрэглэгч',
                'views' => $housing->views,
                'owned' => $request->user()?->id === $housing->user_id,
                'contact_phone' => $request->user() ? $housing->contact_phone : null,
            ],
            'similar' => HousingPost::active()
                ->where('id', '!=', $housing->id)
                ->where('city', $housing->city)
                ->latest()
                ->take(4)
                ->get()
                ->map(fn ($p) => $this->card($p)),
            'seo' => [
                'title' => $housing->title,
                'description' => Str::limit(strip_tags((string) $housing->description), 150) ?: $housing->type_label,
                'image' => $housing->cover,
                'url' => url("/housing/{$housing->slug}"),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Housing/Form', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data = $this->resolveImages($request, $data);
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['status'] = 'active';

        HousingPost::create($data);

        return redirect()->route('housing.my')->with('success', 'Зар нийтлэгдлээ.');
    }

    public function myListings(Request $request): Response
    {
        return Inertia::render('Housing/My', [
            'posts' => HousingPost::where('user_id', $request->user()->id)
                ->latest()
                ->get()
                ->map(fn ($p) => array_merge($this->card($p), ['status' => $p->status, 'views' => $p->views])),
        ]);
    }

    public function edit(Request $request, HousingPost $housing): Response
    {
        $this->authorizeOwner($request, $housing);

        return Inertia::render('Housing/Form', array_merge($this->formOptions(), [
            'post' => array_merge($housing->toArray(), [
                'available_from' => $housing->available_from?->format('Y-m-d'),
            ]),
        ]));
    }

    public function update(Request $request, HousingPost $housing): RedirectResponse
    {
        $this->authorizeOwner($request, $housing);

        $data = $this->validateData($request);
        $data = $this->resolveImages($request, $data, $housing);
        if ($data['title'] !== $housing->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $housing->id);
        }
        $housing->update($data);

        return redirect()->route('housing.my')->with('success', 'Шинэчлэгдлээ.');
    }

    public function close(Request $request, HousingPost $housing): RedirectResponse
    {
        $this->authorizeOwner($request, $housing);
        $housing->update(['status' => $housing->status === 'closed' ? 'active' : 'closed']);

        return back()->with('success', 'Төлөв шинэчлэгдлээ.');
    }

    public function destroy(Request $request, HousingPost $housing): RedirectResponse
    {
        $this->authorizeOwner($request, $housing);
        foreach ($housing->images ?? [] as $url) {
            $this->deleteCoverUrl($url);
        }
        $housing->delete();

        return back()->with('success', 'Устгагдлаа.');
    }

    /**
     * @return array<string, mixed>
     */
    private function card(HousingPost $p): array
    {
        return [
            'id' => $p->id,
            'title' => $p->title,
            'slug' => $p->slug,
            'type_label' => $p->type_label,
            'city' => $p->city,
            'country' => $p->country,
            'district' => $p->district,
            'price' => $p->price,
            'rooms' => $p->rooms,
            'size' => $p->size,
            'furnished' => $p->furnished,
            'cover' => $p->cover,
            'available_from' => $p->available_from?->format('Y.m.d'),
            'created_at' => $p->created_at->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function resolveImages(Request $request, array $data, ?HousingPost $existing = null): array
    {
        $order = $data['image_order'] ?? [];
        unset($data['image_order'], $data['new_images']);

        $stored = [];
        foreach ($request->file('new_images', []) as $i => $file) {
            $stored[$i] = $this->storeResizedImage($file, 'housing');
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
        $final = array_slice($final, 0, 10);

        if ($existing) {
            foreach (array_diff($existing->images ?? [], $final) as $removed) {
                $this->deleteCoverUrl($removed);
            }
        }

        $data['images'] = $final;

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'types' => collect(HousingPost::TYPES)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
            'genders' => collect(HousingPost::GENDERS)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
            'countries' => ['Герман', 'Чех', 'Польш', 'Унгар', 'Австри', 'Франц', 'Бельги', 'Голланд', 'Швед', 'Итали', 'Испани'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(HousingPost::TYPES))],
            'city' => ['required', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:64'],
            'district' => ['nullable', 'string', 'max:120'],
            'price' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'deposit' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'rooms' => ['nullable', 'string', 'max:10'],
            'size' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'available_from' => ['nullable', 'date'],
            'furnished' => ['boolean'],
            'gender_pref' => ['required', Rule::in(array_keys(HousingPost::GENDERS))],
            'description' => ['nullable', 'string', 'max:5000'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'image_order' => ['nullable', 'array', 'max:10'],
            'image_order.*' => ['string', 'max:1024'],
            'new_images' => ['nullable', 'array', 'max:10'],
            'new_images.*' => ['image', 'mimes:jpeg,png,webp', 'max:4096'],
        ]);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'oron-suuts';
        $slug = $base;
        $i = 1;
        while (HousingPost::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private function authorizeOwner(Request $request, HousingPost $housing): void
    {
        abort_unless($housing->user_id === $request->user()->id, 403);
    }
}
