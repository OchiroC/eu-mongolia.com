<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesCoverImage;
use App\Http\Controllers\Controller;
use App\Models\Guide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class GuideController extends Controller
{
    use HandlesCoverImage;

    public function index(): Response
    {
        return Inertia::render('Admin/Guides/Index', [
            'guides' => Guide::latest()
                ->paginate(15)
                ->through(fn ($g) => [
                    'id' => $g->id,
                    'title' => $g->title,
                    'cover_image' => $g->cover_image,
                    'topic_label' => $g->topic_label,
                    'country' => $g->country,
                    'status' => $g->status,
                    'views' => $g->views,
                    'published_at' => $g->published_at?->format('Y.m.d'),
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Guides/Create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data = $this->applyCover($request, $data, 'guides');
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['title']);

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        Guide::create($data);

        return redirect()->route('admin.guides.index')->with('success', 'Заавар үүслээ.');
    }

    public function edit(Guide $guide): Response
    {
        return Inertia::render('Admin/Guides/Edit', array_merge($this->formOptions(), ['guide' => $guide]));
    }

    public function update(Request $request, Guide $guide): RedirectResponse
    {
        $data = $this->validateData($request);
        $data = $this->applyCover($request, $data, 'guides', $guide->cover_image);
        $this->deleteRemovedBodyImages($guide->body, $data['body'] ?? $guide->body);

        if ($data['title'] !== $guide->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $guide->id);
        }
        if ($data['status'] === 'published' && empty($guide->published_at) && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $guide->update($data);

        return redirect()->route('admin.guides.index')->with('success', 'Заавар шинэчлэгдлээ.');
    }

    public function destroy(Guide $guide): RedirectResponse
    {
        $this->deleteCoverUrl($guide->cover_image);
        foreach ($this->bodyImageUrls($guide->body) as $url) {
            $this->deleteCoverUrl($url);
        }
        $guide->delete();

        return redirect()->route('admin.guides.index')->with('success', 'Заавар устгагдлаа.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'topics' => collect(Guide::TOPICS)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
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
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'cover' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:4096'],
            'remove_cover' => ['boolean'],
            'topic' => ['required', Rule::in(array_keys(Guide::TOPICS))],
            'country' => ['nullable', 'string', 'max:64'],
            'is_featured' => ['boolean'],
            'status' => ['required', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
        ]);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'zaavar';
        $slug = $base;
        $i = 1;
        while (Guide::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
