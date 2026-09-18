<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JourneyController extends Controller
{
    /** Бэлтгэлийн жагсаалтын алхмыг хийсэн, хийгээгүй болгож солино. */
    public function toggle(Request $request, Guide $guide): RedirectResponse
    {
        abort_unless($guide->status === 'published' && $guide->stage, 404);

        $request->user()->journeyGuides()->toggle($guide->id);

        return back();
    }
}
