<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class QuestionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Questions/Index', [
            'questions' => Question::with('user:id,name')
                ->latest()
                ->paginate(20)
                ->through(fn ($q) => [
                    'id' => $q->id,
                    'title' => $q->title,
                    'slug' => $q->slug,
                    'category_label' => $q->category_label,
                    'user' => $q->user?->name,
                    'answers' => $q->answers_count,
                    'views' => $q->views,
                    'created_at' => $q->created_at->format('Y.m.d'),
                ]),
        ]);
    }

    public function destroy(Question $question): RedirectResponse
    {
        $question->delete();

        return back()->with('success', 'Асуулт устгагдлаа.');
    }
}
