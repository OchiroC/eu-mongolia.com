<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\AnswerVote;
use App\Models\Question;
use App\Notifications\NewAnswer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class QuestionController extends Controller
{
    public function index(Request $request): Response
    {
        $sort = $request->input('sort', 'latest');

        $questions = Question::with('user:id,name')
            ->when($request->category, fn ($q, $c) => $q->where('category', $c))
            ->when($request->search, fn ($q, $s) =>
                $q->where(fn ($w) => $w->where('title', 'like', "%{$s}%")->orWhere('body', 'like', "%{$s}%")))
            ->when($sort === 'unanswered', fn ($q) => $q->where('answers_count', 0))
            ->when($sort === 'popular', fn ($q) => $q->orderByDesc('answers_count'))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($q) => $this->card($q));

        $counts = Question::selectRaw('category, count(*) as c')->groupBy('category')->pluck('c', 'category');

        return Inertia::render('Questions/Index', [
            'questions' => $questions,
            'categories' => collect(Question::TOPICS)->map(fn ($label, $key) => [
                'key' => $key, 'label' => $label, 'count' => (int) ($counts[$key] ?? 0),
            ])->values(),
            'filters' => array_merge(['sort' => $sort], $request->only(['category', 'search'])),
            'seo' => [
                'title' => 'Асуулт хариулт — Yazguur',
                'description' => 'Европ дахь монголчуудын асуулт хариултын булан. Виз, ажил, орон сууц, өдөр тутмын асуудлаар туслалцаа аваарай.',
            ],
        ]);
    }

    public function show(Request $request, Question $question): Response
    {
        $question->increment('views');
        $question->load('user:id,name,avatar_path');

        $userId = $request->user()?->id;
        $myVotes = $userId
            ? AnswerVote::whereHas('answer', fn ($q) => $q->where('question_id', $question->id))->where('user_id', $userId)->pluck('answer_id')->all()
            : [];

        $answers = $question->answers()
            ->with('user:id,name,avatar_path')
            ->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$question->best_answer_id ?? 0])
            ->orderByDesc('votes_count')
            ->oldest('id')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'body' => $a->body,
                'user' => $a->user?->name ?? 'Хэрэглэгч',
                'avatar' => $a->user?->avatar_url,
                'user_id' => $a->user_id,
                'votes' => $a->votes_count,
                'voted' => in_array($a->id, $myVotes, true),
                'is_best' => $question->best_answer_id === $a->id,
                'created_at' => $a->created_at->diffForHumans(),
            ]);

        return Inertia::render('Questions/Show', [
            'question' => [
                'id' => $question->id,
                'title' => $question->title,
                'slug' => $question->slug,
                'body' => $question->body,
                'category_label' => $question->category_label,
                'country' => $question->country,
                'user' => $question->user?->name ?? 'Хэрэглэгч',
                'avatar' => $question->user?->avatar_url,
                'views' => $question->views,
                'created_at' => $question->created_at->diffForHumans(),
                'owned' => $userId === $question->user_id,
                'best_answer_id' => $question->best_answer_id,
            ],
            'answers' => $answers,
            'seo' => [
                'title' => $question->title,
                'description' => Str::limit(strip_tags($question->body), 150),
                'url' => url("/questions/{$question->slug}"),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Questions/Ask', [
            'categories' => collect(Question::TOPICS)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
            'countries' => ['Герман', 'Чех', 'Польш', 'Унгар', 'Австри', 'Франц', 'Бельги', 'Голланд', 'Швед', 'Итали', 'Испани'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'category' => ['required', Rule::in(array_keys(Question::TOPICS))],
            'country' => ['nullable', 'string', 'max:64'],
        ]);
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['title']);

        $question = Question::create($data);

        return redirect()->route('questions.show', $question)->with('success', 'Асуулт нийтлэгдлээ.');
    }

    public function destroy(Request $request, Question $question): RedirectResponse
    {
        abort_unless($question->user_id === $request->user()->id, 403);
        $question->delete();

        return redirect()->route('questions.index')->with('success', 'Асуулт устгагдлаа.');
    }

    /**
     * @return array<string, mixed>
     */
    private function card(Question $q): array
    {
        return [
            'id' => $q->id,
            'title' => $q->title,
            'slug' => $q->slug,
            'excerpt' => Str::limit(strip_tags($q->body), 140),
            'category_label' => $q->category_label,
            'country' => $q->country,
            'user' => $q->user?->name ?? 'Хэрэглэгч',
            'answers' => $q->answers_count,
            'views' => $q->views,
            'solved' => $q->best_answer_id !== null,
            'created_at' => $q->created_at->diffForHumans(),
        ];
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'asuult';
        $slug = $base;
        $i = 1;
        while (Question::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
