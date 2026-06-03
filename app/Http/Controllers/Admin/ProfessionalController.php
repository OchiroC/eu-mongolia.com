<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesCoverImage;
use App\Http\Controllers\Controller;
use App\Models\Professional;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfessionalController extends Controller
{
    use HandlesCoverImage;

    public function index(Request $request): Response
    {
        $status = $request->input('status', 'pending');
        if (! in_array($status, ['pending', 'active', 'inactive'], true)) {
            $status = 'pending';
        }

        return Inertia::render('Admin/Professionals/Index', [
            'professionals' => Professional::with(['category:id,name', 'user:id,name'])
                ->where('status', $status)
                ->latest()
                ->paginate(20)
                ->through(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'profession' => $p->profession,
                    'photo' => $p->photo,
                    'city' => $p->city,
                    'category' => $p->category?->name,
                    'owner' => $p->user?->name,
                    'is_verified' => $p->is_verified,
                    'is_featured' => $p->isCurrentlyFeatured(),
                    'status' => $p->status,
                    'created_at' => $p->created_at->format('Y.m.d'),
                ]),
            'filter' => $status,
            'counts' => [
                'pending' => Professional::where('status', 'pending')->count(),
                'active' => Professional::where('status', 'active')->count(),
                'inactive' => Professional::where('status', 'inactive')->count(),
            ],
        ]);
    }

    public function approve(Professional $professional): RedirectResponse
    {
        $professional->update(['status' => 'active']);
        $professional->user?->notify(new \App\Notifications\ProfessionalStatus('approved', $professional->name, $professional->slug));

        return back()->with('success', 'Мэргэжилтнийг баталгаажуулж нийтэллээ.');
    }

    public function verify(Professional $professional): RedirectResponse
    {
        $professional->update(['is_verified' => ! $professional->is_verified]);
        if ($professional->is_verified) {
            $professional->user?->notify(new \App\Notifications\ProfessionalStatus('verified', $professional->name, $professional->slug));
        }

        return back()->with('success', 'Баталгаажсан тэмдэг шинэчлэгдлээ.');
    }

    public function feature(Professional $professional): RedirectResponse
    {
        $on = ! $professional->is_featured;
        $professional->update([
            'is_featured' => $on,
            'featured_until' => $on ? now()->addDays(30) : null,
        ]);

        return back()->with('success', $on ? 'Онцлох болголоо.' : 'Онцлохоос хаслаа.');
    }

    public function deactivate(Professional $professional): RedirectResponse
    {
        $professional->update(['status' => $professional->status === 'inactive' ? 'active' : 'inactive']);

        return back()->with('success', 'Төлөв шинэчлэгдлээ.');
    }

    public function destroy(Professional $professional): RedirectResponse
    {
        $this->deleteCoverUrl($professional->photo);
        $professional->delete();

        return back()->with('success', 'Мэргэжилтэн устгагдлаа.');
    }
}
