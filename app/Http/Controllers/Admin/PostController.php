<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesCoverImage;
use App\Http\Controllers\Controller;
use App\Models\NewsCategory;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    use HandlesCoverImage;

    public function index(): Response
    {
        return Inertia::render('Admin/Posts/Index', [
            'posts' => Post::with('category:id,name')
                ->latest()
                ->paginate(15)
                ->through(fn ($p) => [
                    'id' => $p->id,
                    'title' => $p->title,
                    'cover_image' => $p->cover_image,
                    'status' => $p->status,
                    'category' => $p->category?->name,
                    'views' => $p->views,
                    'published_at' => $p->published_at?->format('Y.m.d H:i'),
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Posts/Create', [
            'categories' => $this->orderedCategories(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        $data = $this->applyCover($request, $data, 'news');
        $data = $this->resolveGallery($request, $data);
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['title']);

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $post = Post::create($data);
        $this->syncTags($post, $tags);

        return redirect()->route('admin.posts.index')->with('success', 'Мэдээ үүслээ.');
    }

    public function edit(Post $post): Response
    {
        return Inertia::render('Admin/Posts/Edit', [
            'post' => $post,
            'categories' => $this->orderedCategories(),
            'tags' => $post->tags()->pluck('name'),
        ]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $data = $this->validateData($request);
        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        $data = $this->applyCover($request, $data, 'news', $post->cover_image);
        $data = $this->resolveGallery($request, $data, $post);

        if ($data['title'] !== $post->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $post->id);
        }

        if ($data['status'] === 'published' && empty($post->published_at) && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        // Агуулгаас хасагдсан контент зургуудыг storage-аас устгана.
        $this->deleteRemovedBodyImages($post->body, $data['body'] ?? $post->body);

        $post->update($data);
        $this->syncTags($post, $tags);

        return redirect()->route('admin.posts.index')->with('success', 'Мэдээ шинэчлэгдлээ.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->deleteCoverUrl($post->cover_image);
        foreach ($post->gallery ?? [] as $url) {
            $this->deleteCoverUrl($url);
        }
        // Агуулга доторх бүх контент зургийг устгана.
        foreach ($this->bodyImageUrls($post->body) as $url) {
            $this->deleteCoverUrl($url);
        }
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'Мэдээ устгагдлаа.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'news_category_id' => ['nullable', 'exists:news_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'cover' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:4096'],
            'remove_cover' => ['boolean'],
            'country' => ['nullable', 'string', 'max:64'],
            'is_featured' => ['boolean'],
            'comments_enabled' => ['boolean'],
            'status' => ['required', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
            'tags' => ['nullable', 'array', 'max:15'],
            'tags.*' => ['string', 'max:50'],
            'gallery_order' => ['nullable', 'array', 'max:12'],
            'gallery_order.*' => ['string', 'max:1024'],
            'gallery_files' => ['nullable', 'array', 'max:12'],
            'gallery_files.*' => ['image', 'mimes:jpeg,png,webp', 'max:4096'],
        ]);
    }

    /**
     * Галерейн зургуудыг эрэмбэ (gallery_order) + шинэ upload (gallery_files)-аас бүрдүүлнэ.
     * Токен: "new:N" → gallery_files[N], бусад нь хуучин URL. Хасагдсан зургийг устгана.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function resolveGallery(Request $request, array $data, ?Post $existing = null): array
    {
        $order = $data['gallery_order'] ?? [];
        unset($data['gallery_order'], $data['gallery_files']);

        $stored = [];
        foreach ($request->file('gallery_files', []) as $i => $file) {
            $stored[$i] = $this->storeResizedImage($file, 'news');
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
        $final = array_slice($final, 0, 12);

        if ($existing) {
            foreach (array_diff($existing->gallery ?? [], $final) as $removed) {
                $this->deleteCoverUrl($removed);
            }
        }

        $data['gallery'] = $final;

        return $data;
    }

    /**
     * Мэдээний ангилал — дээд → дэд дарааллаар, depth-тэй (select-д indent хийх).
     */
    private function orderedCategories(): Collection
    {
        $all = NewsCategory::orderBy('sort_order')->get(['id', 'parent_id', 'name']);
        $ordered = collect();

        foreach ($all->whereNull('parent_id') as $parent) {
            $ordered->push(['id' => $parent->id, 'name' => $parent->name, 'depth' => 0]);
            foreach ($all->where('parent_id', $parent->id) as $child) {
                $ordered->push(['id' => $child->id, 'name' => $child->name, 'depth' => 1]);
            }
        }
        // Эцэг нь устсан "өнчин" ангиллыг төгсгөлд нэмнэ.
        foreach ($all as $cat) {
            if (! $ordered->contains('id', $cat->id)) {
                $ordered->push(['id' => $cat->id, 'name' => $cat->name, 'depth' => 0]);
            }
        }

        return $ordered->values();
    }

    /**
     * Тагуудыг нэрээр нь үүсгэж/олж, постонд холбоно.
     *
     * @param  array<int, string>  $names
     */
    private function syncTags(Post $post, array $names): void
    {
        $ids = collect($names)
            ->map(fn ($n) => trim($n))
            ->filter()
            ->unique()
            ->take(15)
            ->map(fn ($n) => Tag::findOrCreateByName($n)->id)
            ->all();

        $post->tags()->sync($ids);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'medee';
        $slug = $base;
        $i = 1;

        while (Post::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
