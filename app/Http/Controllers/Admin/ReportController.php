<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesCoverImage;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Notifications\ListingModerated;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    use HandlesCoverImage;

    public function index(): Response
    {
        return Inertia::render('Admin/Reports/Index', [
            'reports' => Report::with(['listing:id,title,slug,status,city', 'reporter:id,name'])
                ->where('status', 'pending')
                ->latest()
                ->paginate(20)
                ->through(fn ($r) => [
                    'id' => $r->id,
                    'reason' => $r->reason,
                    'note' => $r->note,
                    'reporter' => $r->reporter?->name,
                    'created_at' => $r->created_at->diffForHumans(),
                    'listing' => $r->listing ? [
                        'id' => $r->listing->id,
                        'title' => $r->listing->title,
                        'slug' => $r->listing->slug,
                        'status' => $r->listing->status,
                        'city' => $r->listing->city,
                    ] : null,
                ]),
        ]);
    }

    /** Гомдлыг үндэслэлгүй гэж хаах. */
    public function dismiss(Report $report): RedirectResponse
    {
        $report->update(['status' => 'dismissed']);

        return back()->with('success', 'Гомдлыг хаалаа.');
    }

    /** Зарыг нуух (идэвхгүй болгох) + гомдлыг шийдэгдсэн болгох. */
    public function hide(Report $report): RedirectResponse
    {
        if ($listing = $report->listing) {
            $listing->update(['status' => 'inactive']);
            $listing->user?->notify(new ListingModerated('hidden', $listing->title));
        }
        $report->update(['status' => 'actioned']);

        return back()->with('success', 'Зарыг нууж, гомдлыг шийдлээ.');
    }

    /** Зарыг устгах (зурагтай нь) + гомдлыг шийдэгдсэн болгох. */
    public function destroyListing(Report $report): RedirectResponse
    {
        if ($listing = $report->listing) {
            $listing->user?->notify(new ListingModerated('removed', $listing->title));
            foreach ($listing->images ?? [] as $url) {
                $this->deleteCoverUrl($url);
            }
            $listing->delete();
        }
        $report->update(['status' => 'actioned']);

        return back()->with('success', 'Зарыг устгаж, гомдлыг шийдлээ.');
    }
}
