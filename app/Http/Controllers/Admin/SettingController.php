<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Settings/Index', [
            'settings' => [
                'comments_enabled' => Setting::boolean('comments_enabled', true),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'comments_enabled' => ['required', 'boolean'],
        ]);

        Setting::put('comments_enabled', $data['comments_enabled']);

        return back()->with('success', 'Тохиргоо хадгалагдлаа.');
    }
}
