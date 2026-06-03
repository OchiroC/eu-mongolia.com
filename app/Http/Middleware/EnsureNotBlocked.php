<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotBlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->blocked_at !== null) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->withErrors([
                'email' => 'Таны бүртгэл түр хаагдсан байна. Дэлгэрэнгүйг админаас лавлана уу.',
            ]);
        }

        return $next($request);
    }
}
