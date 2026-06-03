<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class BannerController extends Controller
{
    public function click(Banner $banner): RedirectResponse
    {
        $banner->increment('clicks');

        return redirect()->away($banner->link_url ?: url('/'));
    }

    public function impression(Banner $banner): Response
    {
        $banner->increment('impressions');

        return response()->noContent();
    }
}
