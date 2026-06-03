<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommentController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->input('status', 'pending');
        if (! in_array($status, ['pending', 'approved', 'spam'], true)) {
            $status = 'pending';
        }

        return Inertia::render('Admin/Comments/Index', [
            'comments' => Comment::with(['user:id,name', 'post:id,title,slug'])
                ->where('status', $status)
                ->latest()
                ->paginate(20)
                ->through(fn ($c) => [
                    'id' => $c->id,
                    'body' => $c->body,
                    'status' => $c->status,
                    'is_reply' => $c->parent_id !== null,
                    'user' => $c->user?->name,
                    'post' => $c->post ? ['title' => $c->post->title, 'slug' => $c->post->slug] : null,
                    'created_at' => $c->created_at->diffForHumans(),
                ]),
            'filter' => $status,
            'counts' => [
                'pending' => Comment::where('status', 'pending')->count(),
                'approved' => Comment::where('status', 'approved')->count(),
                'spam' => Comment::where('status', 'spam')->count(),
            ],
        ]);
    }

    public function approve(Comment $comment): RedirectResponse
    {
        $comment->update(['status' => 'approved']);

        return back()->with('success', 'Сэтгэгдлийг зөвшөөрлөө.');
    }

    public function spam(Comment $comment): RedirectResponse
    {
        $comment->update(['status' => 'spam']);

        return back()->with('success', 'Сэтгэгдлийг спам болголоо.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return back()->with('success', 'Сэтгэгдэл устгагдлаа.');
    }
}
