<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BannerController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Banners/Index', [
            'banners' => Banner::with('advertiser:id,name')
                ->latest()
                ->paginate(15)
                ->through(fn ($b) => [
                    'id' => $b->id,
                    'title' => $b->title,
                    'placement' => $b->placement,
                    'status' => $b->status,
                    'is_paid' => $b->is_paid,
                    'price' => $b->price,
                    'advertiser' => $b->advertiser?->name,
                    'impressions' => $b->impressions,
                    'clicks' => $b->clicks,
                    'ends_at' => $b->ends_at?->format('Y.m.d'),
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Banners/Create', [
            'advertisers' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Banner::create($this->validateData($request));

        return redirect()->route('admin.banners.index')->with('success', 'Баннер үүслээ.');
    }

    public function edit(Banner $banner): Response
    {
        return Inertia::render('Admin/Banners/Edit', [
            'banner' => $banner,
            'advertisers' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $banner->update($this->validateData($request));

        return redirect()->route('admin.banners.index')->with('success', 'Баннер шинэчлэгдлээ.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Баннер устгагдлаа.');
    }

    public function setStatus(Request $request, Banner $banner): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:pending,active,rejected,expired']]);
        $banner->update($data);

        return back()->with('success', 'Төлөв шинэчлэгдлээ.');
    }

    /**
     * Mock төлбөр — жинхэнэ карт холбохоос өмнөх түр шийдэл.
     */
    public function pay(Banner $banner): RedirectResponse
    {
        $banner->update(['is_paid' => true, 'status' => 'active']);

        return back()->with('success', 'Төлбөр баталгаажиж, баннер идэвхжлээ. (mock)');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'advertiser_id' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'image_path' => ['required', 'string', 'max:1024'],
            'link_url' => ['nullable', 'url', 'max:1024'],
            'placement' => ['required', 'in:home_top,home_sidebar,news_top,footer'],
            'status' => ['required', 'in:pending,active,rejected,expired'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_paid' => ['boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
