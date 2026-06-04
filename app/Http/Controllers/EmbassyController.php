<?php

namespace App\Http\Controllers;

use App\Models\Embassy;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmbassyController extends Controller
{
    public function index(Request $request): Response
    {
        $missions = Embassy::active()
            ->when($request->country, fn ($q, $c) => $q->where('country', $c))
            ->orderBy('sort_order')
            ->orderBy('country')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'name' => $e->name,
                'kind_label' => $e->kind_label,
                'country' => $e->country,
                'city' => $e->city,
                'address' => $e->address,
                'phone' => $e->phone,
                'emergency_phone' => $e->emergency_phone,
                'email' => $e->email,
                'website' => $e->website,
                'hours' => $e->hours,
                'notes' => $e->notes,
            ]);

        return Inertia::render('Embassy/Index', [
            'missions' => $missions,
            'countries' => Embassy::active()->distinct()->orderBy('country')->pluck('country'),
            'filters' => $request->only(['country']),
            'seo' => [
                'title' => 'Элчин сайдын яам / Яаралтай тусламж — EU Mongolia',
                'description' => 'Европ дахь Монгол улсын элчин сайдын яам, консулын газрын холбоо барих мэдээлэл болон яаралтай тусламжийн дугаарууд.',
            ],
        ]);
    }
}
