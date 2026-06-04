<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Embassy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class EmbassyController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Embassies/Index', [
            'embassies' => Embassy::orderBy('sort_order')->orderBy('country')->get(),
            'kinds' => collect(Embassy::KINDS)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Embassy::create($this->validateData($request));

        return back()->with('success', 'Нэмэгдлээ.');
    }

    public function update(Request $request, Embassy $embassy): RedirectResponse
    {
        $embassy->update($this->validateData($request));

        return back()->with('success', 'Шинэчлэгдлээ.');
    }

    public function destroy(Embassy $embassy): RedirectResponse
    {
        $embassy->delete();

        return back()->with('success', 'Устгагдлаа.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'kind' => ['required', Rule::in(array_keys(Embassy::KINDS))],
            'country' => ['required', 'string', 'max:64'],
            'city' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:60'],
            'emergency_phone' => ['nullable', 'string', 'max:60'],
            'email' => ['nullable', 'email', 'max:160'],
            'website' => ['nullable', 'string', 'max:200'],
            'hours' => ['nullable', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);
    }
}
