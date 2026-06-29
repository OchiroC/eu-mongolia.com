<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class JobController extends Controller
{
    public function index(Request $request): Response
    {
        $jobs = JobPost::active()
            ->when($request->category, fn ($q, $c) => $q->where('category', $c))
            ->when($request->type, fn ($q, $t) => $q->where('employment_type', $t))
            ->when($request->country, fn ($q, $c) => $q->where('country', $c))
            ->when($request->search, fn ($q, $s) =>
                $q->where(fn ($w) => $w->where('title', 'like', "%{$s}%")
                    ->orWhere('company', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($j) => $this->card($j));

        $counts = JobPost::active()->selectRaw('category, count(*) as c')->groupBy('category')->pluck('c', 'category');

        return Inertia::render('Jobs/Index', [
            'jobs' => $jobs,
            'categories' => collect(JobPost::CATEGORIES)->map(fn ($label, $key) => [
                'key' => $key, 'label' => $label, 'count' => (int) ($counts[$key] ?? 0),
            ])->values(),
            'types' => collect(JobPost::TYPES)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
            'countries' => JobPost::active()->whereNotNull('country')->distinct()->orderBy('country')->pluck('country'),
            'filters' => $request->only(['category', 'type', 'country', 'search']),
            'seo' => [
                'title' => 'Ажлын байр — Yazguur',
                'description' => 'Европ дахь монголчуудад зориулсан ажлын зар: бүтэн/цагийн ажил, дадлага, түр ажил.',
            ],
        ]);
    }

    public function show(Request $request, JobPost $job): Response
    {
        abort_unless($job->status === 'active' || $job->user_id === $request->user()?->id, 404);

        $job->increment('views');
        $job->load('author:id,name');

        $isAuth = (bool) $request->user();

        return Inertia::render('Jobs/Show', [
            'job' => [
                'id' => $job->id,
                'title' => $job->title,
                'slug' => $job->slug,
                'company' => $job->company,
                'description' => $job->description,
                'type_label' => $job->type_label,
                'category_label' => $job->category_label,
                'city' => $job->city,
                'country' => $job->country,
                'salary' => $job->salary,
                'status' => $job->status,
                'author' => $job->author?->name,
                'views' => $job->views,
                'created_at' => $job->created_at->toIso8601String(),
                'owned' => $request->user()?->id === $job->user_id,
                // Холбоо барих мэдээллийг зөвхөн нэвтэрсэн хэрэглэгчид.
                'contact' => $isAuth ? [
                    'email' => $job->contact_email,
                    'phone' => $job->contact_phone,
                    'apply_url' => $job->apply_url,
                ] : null,
            ],
            'related' => JobPost::active()
                ->where('id', '!=', $job->id)
                ->where('category', $job->category)
                ->latest()
                ->take(4)
                ->get()
                ->map(fn ($j) => $this->card($j)),
            'seo' => [
                'title' => $job->title.($job->company ? ' — '.$job->company : ''),
                'description' => Str::limit(strip_tags($job->description), 150),
                'url' => url("/jobs/{$job->slug}"),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Jobs/Form', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['status'] = 'active';

        JobPost::create($data);

        return redirect()->route('jobs.my')->with('success', 'Ажлын зар нийтлэгдлээ.');
    }

    public function myJobs(Request $request): Response
    {
        return Inertia::render('Jobs/My', [
            'jobs' => JobPost::where('user_id', $request->user()->id)
                ->latest()
                ->get()
                ->map(fn ($j) => array_merge($this->card($j), ['status' => $j->status, 'views' => $j->views])),
        ]);
    }

    public function edit(Request $request, JobPost $job): Response
    {
        $this->authorizeOwner($request, $job);

        return Inertia::render('Jobs/Form', array_merge($this->formOptions(), ['job' => $job]));
    }

    public function update(Request $request, JobPost $job): RedirectResponse
    {
        $this->authorizeOwner($request, $job);

        $data = $this->validateData($request);
        if ($data['title'] !== $job->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $job->id);
        }
        $job->update($data);

        return redirect()->route('jobs.my')->with('success', 'Ажлын зар шинэчлэгдлээ.');
    }

    public function close(Request $request, JobPost $job): RedirectResponse
    {
        $this->authorizeOwner($request, $job);
        $job->update(['status' => $job->status === 'closed' ? 'active' : 'closed']);

        return back()->with('success', 'Төлөв шинэчлэгдлээ.');
    }

    public function destroy(Request $request, JobPost $job): RedirectResponse
    {
        $this->authorizeOwner($request, $job);
        $job->delete();

        return back()->with('success', 'Ажлын зар устгагдлаа.');
    }

    /**
     * @return array<string, mixed>
     */
    private function card(JobPost $j): array
    {
        return [
            'id' => $j->id,
            'title' => $j->title,
            'slug' => $j->slug,
            'company' => $j->company,
            'type_label' => $j->type_label,
            'category_label' => $j->category_label,
            'city' => $j->city,
            'country' => $j->country,
            'salary' => $j->salary,
            'created_at' => $j->created_at->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'categories' => collect(JobPost::CATEGORIES)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
            'types' => collect(JobPost::TYPES)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
            'countries' => ['Герман', 'Чех', 'Польш', 'Унгар', 'Австри', 'Франц', 'Бельги', 'Голланд', 'Швед', 'Итали', 'Испани'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:5000'],
            'employment_type' => ['required', Rule::in(array_keys(JobPost::TYPES))],
            'category' => ['required', Rule::in(array_keys(JobPost::CATEGORIES))],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:64'],
            'salary' => ['nullable', 'string', 'max:80'],
            'contact_email' => ['nullable', 'email', 'max:160'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'apply_url' => ['nullable', 'string', 'max:200'],
        ]);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'ajil';
        $slug = $base;
        $i = 1;
        while (JobPost::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private function authorizeOwner(Request $request, JobPost $job): void
    {
        abort_unless($job->user_id === $request->user()->id, 403);
    }
}
