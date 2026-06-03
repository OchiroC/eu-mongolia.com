<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Listing;
use App\Models\Post;
use App\Models\Professional;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $urls = [];

        // Статик хуудсууд
        foreach (['/' => '1.0', '/zar' => '0.8', '/news' => '0.8', '/events' => '0.8', '/professionals' => '0.8',
            '/about' => '0.5', '/contact' => '0.5', '/terms' => '0.3', '/privacy' => '0.3'] as $path => $priority) {
            $urls[] = ['loc' => url($path), 'priority' => $priority];
        }

        foreach (Post::published()->latest('published_at')->take(500)->get(['slug', 'updated_at']) as $p) {
            $urls[] = ['loc' => url("/news/{$p->slug}"), 'lastmod' => $p->updated_at?->toAtomString(), 'priority' => '0.6'];
        }

        foreach (Event::published()->latest('starts_at')->take(500)->get(['slug', 'updated_at']) as $e) {
            $urls[] = ['loc' => url("/events/{$e->slug}"), 'lastmod' => $e->updated_at?->toAtomString(), 'priority' => '0.6'];
        }

        foreach (Listing::active()->latest()->take(1000)->get(['slug', 'updated_at']) as $l) {
            $urls[] = ['loc' => url("/zar/{$l->slug}"), 'lastmod' => $l->updated_at?->toAtomString(), 'priority' => '0.5'];
        }

        foreach (Professional::active()->latest()->take(500)->get(['slug', 'updated_at']) as $pro) {
            $urls[] = ['loc' => url("/professionals/{$pro->slug}"), 'lastmod' => $pro->updated_at?->toAtomString(), 'priority' => '0.6'];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $u) {
            $xml .= '  <url><loc>'.e($u['loc']).'</loc>';
            if (! empty($u['lastmod'])) {
                $xml .= '<lastmod>'.$u['lastmod'].'</lastmod>';
            }
            $xml .= '<priority>'.$u['priority'].'</priority></url>'."\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /dashboard',
            'Disallow: /profile',
            'Disallow: /my',
            'Sitemap: '.url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines), 200)->header('Content-Type', 'text/plain');
    }
}
