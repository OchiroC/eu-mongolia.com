<?php

namespace App\Http\Middleware;

use App\Models\Banner;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),
                    'avatar_url' => $user->avatar_url,
                    'phone' => $user->phone,
                    'city' => $user->city,
                    'bio' => $user->bio,
                    'email_verified_at' => $user->email_verified_at,
                ] : null,
                // Хадгалсан зарын ID-ууд (зүрхний идэвхтэй төлөвт)
                'favorites' => $user ? $user->favorites()->pluck('listings.id') : [],
            ],
            // Admin nav badge — шийдэгдээгүй гомдлын тоо
            'pendingReports' => fn () => ($user && $user->hasRole('admin') && Schema::hasTable('reports'))
                ? Report::where('status', 'pending')->count()
                : 0,
            // Admin nav badge — хүлээгдэж буй сэтгэгдлийн тоо
            'pendingComments' => fn () => ($user && $user->hasRole('admin') && Schema::hasTable('comments'))
                ? \App\Models\Comment::where('status', 'pending')->count()
                : 0,
            // Admin nav badge — хүлээгдэж буй мэргэжилтний хүсэлт
            'pendingProfessionals' => fn () => ($user && $user->hasRole('admin') && Schema::hasTable('professionals'))
                ? \App\Models\Professional::where('status', 'pending')->count()
                : 0,
            // Уншаагүй зурвасын тоо (header badge)
            'unreadMessages' => fn () => ($user && Schema::hasTable('messages'))
                ? \App\Models\Message::whereNull('read_at')
                    ->where('sender_id', '!=', $user->id)
                    ->whereHas('conversation', fn ($q) => $q->where('buyer_id', $user->id)->orWhere('seller_id', $user->id))
                    ->count()
                : 0,
            // Мэдэгдэл (нэвтэрсэн хэрэглэгчид)
            'notifications' => fn () => ($user && Schema::hasTable('notifications')) ? [
                'unread' => $user->unreadNotifications()->count(),
                'items' => $user->notifications()->latest()->take(10)->get()->map(fn ($n) => array_merge(
                    is_array($n->data) ? $n->data : [],
                    ['id' => $n->id, 'read' => $n->read_at !== null, 'created_at' => $n->created_at->diffForHumans()],
                )),
            ] : ['unread' => 0, 'items' => []],
            'ziggy' => fn (): array => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'checkin' => fn () => $request->session()->get('checkin'),
                'contact' => fn () => $request->session()->get('contact'),
            ],
            // Идэвхтэй баннеруудыг байршлаар нь бүлэглэж дамжуулна.
            // (Хүснэгт үүсээгүй — шинэ суулгац/тест — үед алгасна.)
            'banners' => fn () => Schema::hasTable('banners')
                ? Banner::query()
                    ->where('status', 'active')->where('is_paid', true)
                    ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()->toDateString()))
                    ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()->toDateString()))
                    ->orderBy('sort_order')
                    ->get(['id', 'title', 'image_path', 'link_url', 'placement'])
                    ->groupBy('placement')
                : [],
        ];
    }
}
