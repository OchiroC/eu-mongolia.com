<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesCoverImage;
use App\Http\Controllers\Controller;
use App\Models\HousingPost;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class HousingController extends Controller
{
    use HandlesCoverImage;

    public function index(): Response
    {
        return Inertia::render('Admin/Housing/Index', [
            'posts' => HousingPost::with('user:id,name')
                ->latest()
                ->paginate(20)
                ->through(fn ($p) => [
                    'id' => $p->id,
                    'title' => $p->title,
                    'slug' => $p->slug,
                    'type_label' => $p->type_label,
                    'city' => $p->city,
                    'price' => $p->price,
                    'user' => $p->user?->name,
                    'status' => $p->status,
                    'created_at' => $p->created_at->format('Y.m.d'),
                ]),
        ]);
    }

    public function close(HousingPost $housing): RedirectResponse
    {
        $housing->update(['status' => $housing->status === 'closed' ? 'active' : 'closed']);

        return back()->with('success', 'Төлөв шинэчлэгдлээ.');
    }

    public function destroy(HousingPost $housing): RedirectResponse
    {
        foreach ($housing->images ?? [] as $url) {
            $this->deleteCoverUrl($url);
        }
        $housing->delete();

        return back()->with('success', 'Устгагдлаа.');
    }
}
