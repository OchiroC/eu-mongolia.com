<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class GuideController extends Controller
{
    public function index(Request $request): Response
    {
        $guides = Guide::published()
            ->when($request->topic, fn ($q, $t) => $q->where('topic', $t))
            ->when($request->country, fn ($q, $c) => $q->where('country', $c))
            ->when($request->search, fn ($q, $s) =>
                $q->where(fn ($w) => $w->where('title', 'like', "%{$s}%")->orWhere('excerpt', 'like', "%{$s}%")))
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString()
            ->through(fn ($g) => $this->card($g));

        $counts = Guide::published()->selectRaw('topic, count(*) as c')->groupBy('topic')->pluck('c', 'topic');

        return Inertia::render('Guides/Index', [
            'guides' => $guides,
            'topics' => collect(Guide::TOPICS)->map(fn ($label, $key) => [
                'key' => $key,
                'label' => $label,
                'count' => (int) ($counts[$key] ?? 0),
            ])->values(),
            'countries' => Guide::published()->whereNotNull('country')->distinct()->orderBy('country')->pluck('country'),
            'filters' => $request->only(['topic', 'country', 'search']),
            'seo' => [
                'title' => 'Заавар / Гарын авлага — EU Mongolia',
                'description' => 'Европ дахь монголчуудад зориулсан виз, бүртгэл, даатгал, татвар, жолооны үнэмлэх зэрэг алхам алхмын заавар.',
            ],
        ]);
    }

    public function show(Guide $guide): Response
    {
        abort_unless($guide->status === 'published', 404);

        $guide->increment('views');
        $guide->load('author:id,name');

        $related = Guide::published()
            ->where('id', '!=', $guide->id)
            ->where('topic', $guide->topic)
            ->latest('published_at')
            ->take(4)
            ->get(['id', 'title', 'slug', 'topic', 'country', 'published_at']);

        return Inertia::render('Guides/Show', [
            'guide' => [
                'id' => $guide->id,
                'title' => $guide->title,
                'slug' => $guide->slug,
                'body' => $guide->body,
                'cover_image' => $guide->cover_image,
                'topic' => $guide->topic,
                'topic_label' => $guide->topic_label,
                'country' => $guide->country,
                'author' => $guide->author?->name,
                'views' => $guide->views,
                'published_at' => $guide->published_at?->toIso8601String(),
                'updated_at' => $guide->updated_at?->toIso8601String(),
            ],
            'related' => $related->map(fn ($g) => [
                'id' => $g->id, 'title' => $g->title, 'slug' => $g->slug,
                'topic_label' => $g->topic_label, 'country' => $g->country,
            ]),
            'seo' => [
                'title' => $guide->title,
                'description' => $guide->excerpt ?: Str::limit(strip_tags($guide->body), 150),
                'image' => $guide->cover_image,
                'url' => url("/guides/{$guide->slug}"),
                'jsonld' => array_filter([
                    '@context' => 'https://schema.org',
                    '@type' => 'Article',
                    'headline' => $guide->title,
                    'image' => $guide->cover_image,
                    'datePublished' => $guide->published_at?->toAtomString(),
                    'dateModified' => $guide->updated_at?->toAtomString(),
                    'author' => $guide->author ? ['@type' => 'Person', 'name' => $guide->author->name] : null,
                    'publisher' => ['@type' => 'Organization', 'name' => 'EU Mongolia'],
                ]),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function card(Guide $g): array
    {
        return [
            'id' => $g->id,
            'title' => $g->title,
            'slug' => $g->slug,
            'excerpt' => $g->excerpt,
            'cover_image' => $g->cover_image,
            'topic' => $g->topic,
            'topic_label' => $g->topic_label,
            'country' => $g->country,
            'is_featured' => $g->is_featured,
            'published_at' => $g->published_at?->toIso8601String(),
        ];
    }
}
