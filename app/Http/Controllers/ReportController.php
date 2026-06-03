<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Report;
use App\Models\User;
use App\Notifications\NewReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ReportController extends Controller
{
    public function store(Request $request, Listing $listing): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'in:spam,scam,prohibited,duplicate,offensive,other'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        // Нэг хэрэглэгч нэг зарыг давхар (шийдэгдээгүй) гомдол гаргахаас сэргийлнэ.
        $already = Report::where('listing_id', $listing->id)
            ->where('reporter_id', $request->user()->id)
            ->where('status', 'pending')
            ->exists();

        if (! $already) {
            $report = Report::create([
                'listing_id' => $listing->id,
                'reporter_id' => $request->user()->id,
                'reason' => $data['reason'],
                'note' => $data['note'] ?? null,
            ]);

            // Бүх админд мэдэгдэнэ.
            Notification::send(User::role('admin')->get(), new NewReport($report));
        }

        return back()->with('success', 'Гомдол хүлээн авлаа. Баярлалаа.');
    }
}
