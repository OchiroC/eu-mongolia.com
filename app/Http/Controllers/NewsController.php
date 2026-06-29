<?php

namespace App\Http\Controllers;

use App\Models\NewsCategory;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NewsController extends Controller
{
    public function index(Request $request): Response
    {
        $posts = Post::published()
            ->with(['category:id,name,slug', 'author:id,name'])
            ->when($request->category, fn ($q, $slug) =>
                $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($request->country, fn ($q, $country) => $q->where('country', $country))
            ->when($request->tag, fn ($q, $slug) =>
                $q->whereHas('tags', fn ($t) => $t->where('slug', $slug)))
            ->when($request->search, fn ($q, $s) =>
                $q->where(fn ($w) => $w->where('title', 'like', "%{$s}%")
                    ->orWhere('excerpt', 'like', "%{$s}%")))
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('News/Index', [
            'posts' => $posts,
            'categories' => NewsCategory::orderBy('sort_order')->get(['id', 'parent_id', 'name', 'slug']),
            'popularTags' => Tag::withCount(['posts' => fn ($q) => $q->where('status', 'published')])
                ->whereHas('posts', fn ($q) => $q->where('status', 'published'))
                ->orderByDesc('posts_count')
                ->take(15)
                ->get(['id', 'name', 'slug']),
            'activeTag' => $request->tag ? Tag::where('slug', $request->tag)->value('name') : null,
            'filters' => $request->only(['category', 'country', 'search', 'tag']),
        ]);
    }

    public function show(Request $request, Post $post): Response
    {
        abort_unless($post->status === 'published', 404);

        $post->increment('views');
        $post->load(['category:id,name,slug', 'author:id,name', 'tags:id,name,slug']);

        $related = Post::published()
            ->where('id', '!=', $post->id)
            ->where('news_category_id', $post->news_category_id)
            ->latest('published_at')
            ->take(4)
            ->get(['id', 'title', 'slug', 'cover_image', 'published_at']);

        $commentsOpen = \App\Models\Setting::boolean('comments_enabled', true) && $post->comments_enabled;

        return Inertia::render('News/Show', [
            'post' => $post,
            'related' => $related,
            'commentsEnabled' => $commentsOpen,
            'comments' => $this->buildComments($post, $request->user()?->id),
            'categories' => \App\Models\NewsCategory::orderBy('sort_order')
                ->withCount(['posts' => fn ($q) => $q->where('status', 'published')])
                ->get(['id', 'name', 'slug']),
            'seo' => [
                'title' => $post->title,
                'description' => $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 150),
                'image' => $post->cover_image,
                'url' => url("/news/{$post->slug}"),
                'jsonld' => array_filter([
                    '@context' => 'https://schema.org',
                    '@type' => 'NewsArticle',
                    'headline' => $post->title,
                    'image' => $post->cover_image,
                    'datePublished' => $post->published_at?->toAtomString(),
                    'dateModified' => $post->updated_at?->toAtomString(),
                    'author' => $post->author ? ['@type' => 'Person', 'name' => $post->author->name] : null,
                    'publisher' => ['@type' => 'Organization', 'name' => 'Yazguur'],
                    'mainEntityOfPage' => url("/news/{$post->slug}"),
                ]),
            ],
        ]);
    }

    /**
     * Зөвшөөрөгдсөн сэтгэгдлүүдийг (эх + хариу) бүтэцтэйгээр буцаана.
     * Тухайн хэрэглэгчийн like/dislike-ийг тэмдэглэнэ.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildComments(Post $post, ?int $userId): array
    {
        $roots = $post->comments()
            ->approved()
            ->roots()
            ->with([
                'user:id,name,avatar_path',
                'replies' => fn ($q) => $q->approved()->with('user:id,name,avatar_path')->oldest(),
            ])
            ->latest()
            ->get();

        $allIds = $roots->pluck('id')
            ->merge($roots->flatMap(fn ($c) => $c->replies->pluck('id')))
            ->all();

        $mine = $userId
            ? \App\Models\CommentReaction::where('user_id', $userId)->whereIn('comment_id', $allIds)->pluck('value', 'comment_id')
            : collect();

        $map = function ($c) use ($mine) {
            return [
                'id' => $c->id,
                'body' => $c->body,
                'user' => $c->user?->name ?? 'Хэрэглэгч',
                'avatar' => $c->user?->avatar_url,
                'user_id' => $c->user_id,
                'likes' => $c->likes_count,
                'dislikes' => $c->dislikes_count,
                'my_reaction' => (int) ($mine[$c->id] ?? 0),
                'created_at' => $c->created_at->diffForHumans(),
            ];
        };

        return $roots->map(fn ($c) => array_merge($map($c), [
            'replies' => $c->replies->map($map)->values(),
        ]))->values()->all();
    }
}
