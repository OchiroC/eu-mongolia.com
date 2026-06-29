<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $seo = $page['props']['seo'] ?? [];
            $seoTitle = $seo['title'] ?? config('app.name', 'Yazguur');
            $seoDesc = $seo['description'] ?? 'Европ дахь монголчуудын зар, мэдээ, эвентийн нэгдсэн платформ.';
            $seoImage = $seo['image'] ?? null;
            $seoUrl = $seo['url'] ?? url()->current();
            $seoJsonLd = $seo['jsonld'] ?? null;
        @endphp

        <title inertia>{{ $seoTitle }}</title>
        <meta name="description" content="{{ $seoDesc }}">
        <link rel="canonical" href="{{ $seoUrl }}">
        <meta name="theme-color" content="#2563eb">

        {{-- Favicon / brand --}}
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <link rel="apple-touch-icon" href="/favicon.svg">
        <link rel="manifest" href="/site.webmanifest" crossorigin="use-credentials">

        {{-- Open Graph / Twitter (нийгмийн сүлжээнд хуваалцахад) --}}
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Yazguur">
        <meta property="og:title" content="{{ $seoTitle }}">
        <meta property="og:description" content="{{ $seoDesc }}">
        <meta property="og:url" content="{{ $seoUrl }}">
        @if($seoImage)<meta property="og:image" content="{{ $seoImage }}">@endif
        <meta name="twitter:card" content="{{ $seoImage ? 'summary_large_image' : 'summary' }}">
        <meta name="twitter:title" content="{{ $seoTitle }}">
        <meta name="twitter:description" content="{{ $seoDesc }}">
        @if($seoImage)<meta name="twitter:image" content="{{ $seoImage }}">@endif

        {{-- Structured data (Google rich results) --}}
        @if($seoJsonLd)
        <script type="application/ld+json">{!! json_encode($seoJsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
        @endif

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
