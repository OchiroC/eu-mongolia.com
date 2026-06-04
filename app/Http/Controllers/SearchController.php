<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Event;
use App\Models\Guide;
use App\Models\HousingPost;
use App\Models\JobPost;
use App\Models\Listing;
use App\Models\Post;
use App\Models\Professional;
use App\Models\Question;
use App\Models\Ride;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function index(Request $request): Response
    {
        $q = trim((string) $request->input('q'));
        $groups = [];

        if (mb_strlen($q) >= 2) {
            $like = '%'.$q.'%';

            $groups = collect([
                $this->group('Зар', '/zar', Listing::active()
                    ->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('description', 'like', $like))
                    ->latest()->take(5)->get()
                    ->map(fn ($l) => ['title' => $l->title, 'subtitle' => $l->city, 'url' => "/zar/{$l->slug}"])),

                $this->group('Орон сууц', '/housing', HousingPost::active()
                    ->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('description', 'like', $like))
                    ->latest()->take(5)->get()
                    ->map(fn ($p) => ['title' => $p->title, 'subtitle' => $p->city, 'url' => "/housing/{$p->slug}"])),

                $this->group('Ажил', '/jobs', JobPost::active()
                    ->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('company', 'like', $like))
                    ->latest()->take(5)->get()
                    ->map(fn ($j) => ['title' => $j->title, 'subtitle' => $j->company, 'url' => "/jobs/{$j->slug}"])),

                $this->group('Бизнес', '/businesses', Business::active()
                    ->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('description', 'like', $like))
                    ->latest()->take(5)->get()
                    ->map(fn ($b) => ['title' => $b->name, 'subtitle' => $b->city, 'url' => "/businesses/{$b->slug}"])),

                $this->group('Туслах', '/professionals', Professional::active()
                    ->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('profession', 'like', $like))
                    ->latest()->take(5)->get()
                    ->map(fn ($p) => ['title' => $p->name, 'subtitle' => $p->profession, 'url' => "/professionals/{$p->slug}"])),

                $this->group('Guide', '/guides', Guide::published()
                    ->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('excerpt', 'like', $like))
                    ->latest('published_at')->take(5)->get()
                    ->map(fn ($g) => ['title' => $g->title, 'subtitle' => $g->country, 'url' => "/guides/{$g->slug}"])),

                $this->group('Мэдээ', '/news', Post::published()
                    ->where('title', 'like', $like)
                    ->latest('published_at')->take(5)->get()
                    ->map(fn ($p) => ['title' => $p->title, 'subtitle' => null, 'url' => "/news/{$p->slug}"])),

                $this->group('Эвент', '/events', Event::where('status', 'published')
                    ->where('title', 'like', $like)
                    ->latest('starts_at')->take(5)->get()
                    ->map(fn ($e) => ['title' => $e->title, 'subtitle' => $e->city, 'url' => "/events/{$e->slug}"])),

                $this->group('Асуулт', '/questions', Question::where('title', 'like', $like)
                    ->latest()->take(5)->get()
                    ->map(fn ($q) => ['title' => $q->title, 'subtitle' => null, 'url' => "/questions/{$q->slug}"])),

                $this->group('Аялал', '/rides', Ride::active()->upcoming()
                    ->where(fn ($w) => $w->where('from_city', 'like', $like)->orWhere('to_city', 'like', $like))
                    ->latest()->take(5)->get()
                    ->map(fn ($r) => ['title' => "{$r->from_city} → {$r->to_city}", 'subtitle' => null, 'url' => "/rides/{$r->id}"])),
            ])->filter()->values()->all();
        }

        return Inertia::render('Search/Index', [
            'q' => $q,
            'groups' => $groups,
            'total' => collect($groups)->sum(fn ($g) => count($g['items'])),
            'seo' => ['title' => $q !== '' ? "Хайлт: {$q}" : 'Хайлт'],
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $items
     * @return array<string, mixed>|null
     */
    private function group(string $label, string $href, $items): ?array
    {
        if ($items->isEmpty()) {
            return null;
        }

        return ['label' => $label, 'href' => $href, 'items' => $items->values()->all()];
    }
}
