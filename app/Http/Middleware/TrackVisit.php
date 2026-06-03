<?php

namespace App\Http\Middleware;

use App\Models\Visit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TrackVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            if ($this->shouldLog($request) && Schema::hasTable('visits')) {
                Visit::create([
                    'session_id' => $request->hasSession() ? $request->session()->getId() : null,
                    'ip' => $request->ip(),
                    'path' => Str::limit($request->path(), 250, ''),
                    'user_id' => $request->user()?->id,
                    'created_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            // Зочдын бүртгэл амжилтгүй болсон ч хуудсыг тасалдуулахгүй.
        }

        return $response;
    }

    /**
     * Зөвхөн нийтийн сайтын GET хуудсыг тооцно (админ/auth/health биш).
     */
    private function shouldLog(Request $request): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        return ! $request->is(
            'admin', 'admin/*',
            'login', 'register', 'logout', 'confirm-password',
            'forgot-password', 'reset-password', 'reset-password/*',
            'verify-email/*', 'email/*',
            'up', 'banners/*',
        );
    }
}
