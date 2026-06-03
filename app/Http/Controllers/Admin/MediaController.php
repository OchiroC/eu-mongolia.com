<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesCoverImage;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    use HandlesCoverImage;

    /**
     * Засварлагчид (Tiptap) зураг upload хийж, ашиглах URL-ийг буцаана.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image', 'mimes:jpeg,png,webp,gif', 'max:6144'],
        ]);

        $url = $this->storeResizedImage($request->file('file'), 'content');

        return response()->json(['url' => $url]);
    }
}
