<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        return Inertia::render('Admin/Users/Index', [
            'filters' => ['search' => $search],
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
                    'blocked' => $u->blocked_at !== null,
                    'listings_count' => $u->listings_count,
                    'created_at' => $u->created_at->format('Y.m.d'),
                ]),
        ]);
    }

    public function toggleRole(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Өөрийн дүрийг өөрчлөх боломжгүй.');
        }

        $user->hasRole('admin') ? $user->removeRole('admin') : $user->assignRole('admin');

        return back()->with('success', 'Хэрэглэгчийн дүр шинэчлэгдлээ.');
    }

    public function toggleBlock(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Өөрийгөө блоклох боломжгүй.');
        }

        $user->blocked_at = $user->blocked_at ? null : now();
        $user->save();

        return back()->with('success', $user->blocked_at ? 'Хэрэглэгчийг блоклов.' : 'Блокыг цуцаллаа.');
    }
}
