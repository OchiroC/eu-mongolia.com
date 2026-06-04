<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobPost;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class JobController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Jobs/Index', [
            'jobs' => JobPost::with('author:id,name')
                ->latest()
                ->paginate(20)
                ->through(fn ($j) => [
                    'id' => $j->id,
                    'title' => $j->title,
                    'slug' => $j->slug,
                    'company' => $j->company,
                    'category_label' => $j->category_label,
                    'type_label' => $j->type_label,
                    'city' => $j->city,
                    'country' => $j->country,
                    'author' => $j->author?->name,
                    'status' => $j->status,
                    'views' => $j->views,
                    'created_at' => $j->created_at->format('Y.m.d'),
                ]),
        ]);
    }

    public function close(JobPost $job): RedirectResponse
    {
        $job->update(['status' => $job->status === 'closed' ? 'active' : 'closed']);

        return back()->with('success', 'Төлөв шинэчлэгдлээ.');
    }

    public function destroy(JobPost $job): RedirectResponse
    {
        $job->delete();

        return back()->with('success', 'Ажлын зар устгагдлаа.');
    }
}
