<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesCoverImage;
use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BusinessController extends Controller
{
    use HandlesCoverImage;

    public function index(Request $request): Response
    {
        $status = $request->input('status', 'pending');
        if (! in_array($status, ['pending', 'active', 'inactive'], true)) {
            $status = 'pending';
        }

        return Inertia::render('Admin/Businesses/Index', [
            'businesses' => Business::with('user:id,name')
                ->where('status', $status)
                ->latest()
                ->paginate(20)
                ->through(fn ($b) => [
                    'id' => $b->id,
                    'name' => $b->name,
                    'slug' => $b->slug,
                    'category_label' => $b->category_label,
                    'city' => $b->city,
                    'photo' => $b->photo,
                    'owner' => $b->user?->name,
                    'is_featured' => $b->isCurrentlyFeatured(),
                    'status' => $b->status,
                ]),
            'filter' => $status,
            'counts' => [
                'pending' => Business::where('status', 'pending')->count(),
                'active' => Business::where('status', 'active')->count(),
                'inactive' => Business::where('status', 'inactive')->count(),
            ],
        ]);
    }

    public function approve(Business $business): RedirectResponse
    {
        $business->update(['status' => 'active']);

        return back()->with('success', 'Нийтэллээ.');
    }

    public function feature(Business $business): RedirectResponse
    {
        $on = ! $business->is_featured;
        $business->update(['is_featured' => $on, 'featured_until' => $on ? now()->addDays(30) : null]);

        return back()->with('success', $on ? 'Онцлох болголоо.' : 'Онцлохоос хаслаа.');
    }

    public function deactivate(Business $business): RedirectResponse
    {
        $business->update(['status' => $business->status === 'inactive' ? 'active' : 'inactive']);

        return back()->with('success', 'Төлөв шинэчлэгдлээ.');
    }

    public function destroy(Business $business): RedirectResponse
    {
        $this->deleteCoverUrl($business->photo);
        $business->delete();

        return back()->with('success', 'Устгагдлаа.');
    }
}
