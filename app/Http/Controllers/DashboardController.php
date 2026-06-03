<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Event;
use App\Models\Listing;
use App\Models\Order;
use App\Models\Post;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Хэрэглэгч admin бол удирдлагын самбар, үгүй бол хувийн самбар руу.
     */
    public function index(Request $request): Response
    {
        return $request->user()->hasRole('admin')
            ? $this->admin()
            : $this->user($request);
    }

    /**
     * Админ самбар — нэгдсэн статистик.
     */
    private function admin(): Response
    {
        $paidOrders = Order::where('status', 'paid');

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'revenue' => (float) (clone $paidOrders)->sum('total'),
                'orders_paid' => (clone $paidOrders)->count(),
                'orders_pending' => Order::where('status', 'pending')->count(),
                'tickets_sold' => Ticket::whereHas('order', fn ($q) => $q->where('status', 'paid'))->count(),
                'posts' => Post::count(),
                'posts_published' => Post::where('status', 'published')->count(),
                'events' => Event::count(),
                'events_upcoming' => Event::where('starts_at', '>=', now())->count(),
                'banners_active' => Banner::where('status', 'active')->where('is_paid', true)->count(),
                'banners_pending' => Banner::where('status', 'pending')->count(),
                'users' => User::count(),
                'banner_impressions' => (int) Banner::sum('impressions'),
                'banner_clicks' => (int) Banner::sum('clicks'),
                // Зар (маркетплейс)
                'listings' => Listing::where('status', 'active')->count(),
                'listings_total' => Listing::count(),
                'listings_today' => Listing::whereDate('created_at', today())->count(),
                // Зочдын статистик
                'visitors' => Schema::hasTable('visits') ? (int) Visit::distinct('session_id')->count('session_id') : 0,
                'visits_total' => Schema::hasTable('visits') ? Visit::count() : 0,
                'visits_today' => Schema::hasTable('visits') ? Visit::whereDate('created_at', today())->count() : 0,
            ],
            'visitSeries' => $this->visitSeries(),
            // Шинэ зар (сүүлд нэмэгдсэн)
            'recentListings' => Listing::with('category:id,name')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($l) => [
                    'id' => $l->id,
                    'slug' => $l->slug,
                    'title' => $l->title,
                    'price' => $l->price,
                    'price_type' => $l->price_type,
                    'city' => $l->city,
                    'status' => $l->status,
                    'cover' => $l->cover,
                    'category' => $l->category?->name,
                    'created_at' => $l->created_at->toIso8601String(),
                ]),
            // Хамгийн их зочилсон хуудас — хэсгээр бүлэглэсэн (сүүлийн 30 хоног)
            'topPages' => $this->topPages(),
            'recentOrders' => Order::with(['event:id,title', 'user:id,name'])
                ->latest()
                ->take(6)
                ->get()
                ->map(fn ($o) => [
                    'id' => $o->id,
                    'reference' => $o->reference,
                    'event' => $o->event?->title,
                    'buyer' => $o->buyer_name ?? $o->user?->name,
                    'total' => $o->total,
                    'status' => $o->status,
                    'created_at' => $o->created_at->diffForHumans(),
                ]),
            'topEvents' => Event::withSum('ticketTypes', 'sold')
                ->withSum(['orders as revenue' => fn ($q) => $q->where('status', 'paid')], 'total')
                ->orderByDesc('ticket_types_sum_sold')
                ->take(5)
                ->get()
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'title' => $e->title,
                    'sold' => (int) $e->ticket_types_sum_sold,
                    'revenue' => (float) ($e->revenue ?? 0),
                ])
                ->filter(fn ($e) => $e['sold'] > 0)
                ->values(),
            'pendingBanners' => Banner::where('status', 'pending')
                ->latest()
                ->take(5)
                ->get(['id', 'title', 'placement', 'price']),
        ]);
    }

    /**
     * Хэрэглэгчийн хувийн самбар — Зар гол, тасалбар хажууд.
     */
    private function user(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Dashboard', [
            'stats' => [
                'listings' => Listing::where('user_id', $user->id)->count(),
                'listings_active' => Listing::where('user_id', $user->id)->where('status', 'active')->count(),
                'listings_sold' => Listing::where('user_id', $user->id)->where('status', 'sold')->count(),
                'tickets' => Ticket::whereHas('order', fn ($q) =>
                    $q->where('user_id', $user->id)->where('status', 'paid'))->count(),
            ],
            // Гол — миний зар
            'myListings' => Listing::where('user_id', $user->id)
                ->with('category:id,name')
                ->latest()
                ->take(6)
                ->get()
                ->map(fn ($l) => [
                    'id' => $l->id,
                    'slug' => $l->slug,
                    'title' => $l->title,
                    'price' => $l->price,
                    'price_type' => $l->price_type,
                    'status' => $l->status,
                    'views' => $l->views,
                    'cover' => $l->cover,
                ]),
            // Хажуу — авсан тасалбар
            'upcomingTickets' => Order::where('user_id', $user->id)
                ->where('status', 'paid')
                ->whereHas('event', fn ($q) => $q->where('starts_at', '>=', now()))
                ->with(['event:id,title,slug,starts_at,venue,city'])
                ->withCount('tickets')
                ->get()
                ->sortBy(fn ($o) => $o->event?->starts_at)
                ->values()
                ->map(fn ($o) => [
                    'id' => $o->id,
                    'event' => $o->event?->title,
                    'starts_at' => $o->event?->starts_at,
                    'tickets_count' => $o->tickets_count,
                ]),
        ]);
    }

    /**
     * Сүүлийн 14 хоногийн зочдын цуваа (график-д), өдрөөр.
     *
     * @return array<int, array{label: string, total: int}>
     */
    private function visitSeries(): array
    {
        $series = [];
        $hasTable = Schema::hasTable('visits');

        for ($i = 13; $i >= 0; $i--) {
            $day = now()->subDays($i);

            $count = $hasTable
                ? Visit::whereBetween('created_at', [
                    $day->copy()->startOfDay(),
                    $day->copy()->endOfDay(),
                ])->count()
                : 0;

            $series[] = [
                'label' => $day->translatedFormat('m/d'),
                'total' => $count,
            ];
        }

        return $series;
    }

    /**
     * Зочлолтыг хуудасны хэсгээр (section) бүлэглэж, их үзсэнээр эрэмбэлнэ.
     *
     * @return array<int, array{label: string, hits: int}>
     */
    private function topPages(): array
    {
        if (! Schema::hasTable('visits')) {
            return [];
        }

        return Visit::selectRaw('path, COUNT(*) as hits')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('path')
            ->get()
            ->groupBy(fn ($row) => $this->sectionLabel($row->path))
            ->map(fn ($group, $label) => ['label' => $label, 'hits' => (int) $group->sum('hits')])
            ->sortByDesc('hits')
            ->values()
            ->all();
    }

    private function sectionLabel(?string $path): string
    {
        $segment = explode('/', trim((string) $path, '/'))[0] ?? '';

        return match ($segment) {
            '' => 'Нүүр',
            'zar' => 'Зар',
            'news' => 'Мэдээ',
            'events' => 'Эвент',
            'dashboard' => 'Самбар',
            'profile' => 'Профайл',
            'my' => 'Миний',
            'orders' => 'Захиалга',
            default => ucfirst($segment),
        };
    }
}
