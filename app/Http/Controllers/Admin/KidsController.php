<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KidsResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class KidsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Kids/Index', [
            'resources' => KidsResource::orderBy('sort_order')->latest()->get(),
            'categories' => collect(KidsResource::CATEGORIES)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        KidsResource::create($this->validateData($request));

        return back()->with('success', 'Нэмэгдлээ.');
    }

    public function update(Request $request, KidsResource $kid): RedirectResponse
    {
        $kid->update($this->validateData($request));

        return back()->with('success', 'Шинэчлэгдлээ.');
    }

    public function destroy(KidsResource $kid): RedirectResponse
    {
        $kid->delete();

        return back()->with('success', 'Устгагдлаа.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category' => ['required', Rule::in(array_keys(KidsResource::CATEGORIES))],
            'description' => ['nullable', 'string', 'max:1500'],
            'url' => ['nullable', 'string', 'max:300'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:64'],
            'contact' => ['nullable', 'string', 'max:160'],
            'age_range' => ['nullable', 'string', 'max:40'],
            'is_featured' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);
    }
}
