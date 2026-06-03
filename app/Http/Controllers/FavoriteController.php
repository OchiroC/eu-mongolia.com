<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FavoriteController extends Controller
{
    /**
     * Хадгалсан зарууд.
     */
    public function index(Request $request): Response
    {
        $listings = $request->user()->favorites()
            ->with('category:id,name,slug,icon')
            ->latest('listing_user.created_at')
            ->get()
            ->map(fn ($l) => [
                'id' => $l->id,
                'title' => $l->title,
                'slug' => $l->slug,
                'price' => $l->price,
                'price_type' => $l->price_type,
                'city' => $l->city,
                'postal_code' => $l->postal_code,
                'cover' => $l->cover,
                'is_featured' => $l->is_featured,
                'status' => $l->status,
                'created_at' => $l->created_at->toIso8601String(),
            ]);

        return Inertia::render('Zar/Favorites', ['listings' => $listings]);
    }

    /**
     * Хадгалах / хасах (toggle).
     */
    public function toggle(Request $request, Listing $listing): RedirectResponse
    {
        $request->user()->favorites()->toggle($listing->id);

        return back();
    }
}
