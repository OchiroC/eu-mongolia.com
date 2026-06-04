<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /** Ажилтанд олгож болох дүрүүд (admin-ийг UI-аас олгох/хасах боломжгүй). */
    public const STAFF_ROLES = [
        'editor' => 'Сэтгүүлч (мэдээ, Guide)',
        'moderator' => 'Модератор (сэтгэгдэл, гомдол, асуулт)',
        'organizer' => 'Эвент зохион байгуулагч',
        'advertiser' => 'Сурталчлагч (баннер)',
        'user' => 'Энгийн хэрэглэгч',
    ];

    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        return Inertia::render('Admin/Users/Index', [
            'filters' => ['search' => $search],
            'roleOptions' => collect(self::STAFF_ROLES)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
            'users' => User::withCount('listings')
                ->when($search, fn ($q) => $q->where(fn ($w) => $w
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")))
                ->latest()
                ->paginate(20)
                ->withQueryString()
                ->through(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'avatar_url' => $u->avatar_url,
                    'is_admin' => $u->hasRole('admin'),
                    'role' => $u->getRoleNames()->first(),
                    'blocked' => $u->blocked_at !== null,
                    'listings_count' => $u->listings_count,
                    'created_at' => $u->created_at->format('Y.m.d'),
                ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', Rule::in(array_keys(self::STAFF_ROLES))],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);
        $user->syncRoles([$data['role']]);

        return back()->with('success', 'Ажилтан үүслээ.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->guardAdmin($user);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', Password::defaults()],
            'role' => ['required', Rule::in(array_keys(self::STAFF_ROLES))],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            ...(! empty($data['password']) ? ['password' => Hash::make($data['password'])] : []),
        ]);
        $user->syncRoles([$data['role']]);

        return back()->with('success', 'Ажилтны мэдээлэл шинэчлэгдлээ.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->guardAdmin($user);

        $user->delete();

        return back()->with('success', 'Хэрэглэгч устгагдлаа.');
    }

    public function toggleBlock(User $user): RedirectResponse
    {
        $this->guardAdmin($user);

        $user->blocked_at = $user->blocked_at ? null : now();
        $user->save();

        return back()->with('success', $user->blocked_at ? 'Хэрэглэгчийг блоклов.' : 'Блокыг цуцаллаа.');
    }

    /**
     * Админ дүртэй хэрэглэгчийг (өөрийгөө оруулаад) засах/устгах/блоклохыг хориглоно.
     */
    private function guardAdmin(User $user): void
    {
        abort_if($user->hasRole('admin'), 403, 'Админ бүртгэлийг өөрчлөх боломжгүй.');
    }
}
