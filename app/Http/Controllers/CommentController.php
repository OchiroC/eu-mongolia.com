<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\NewComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class CommentController extends Controller
{
    /** Сэтгэгдэл (эсвэл хариу) нэмэх — модерацид pending төлөвтэй орно. */
    public function store(Request $request, Post $post): RedirectResponse
    {
        $this->ensureCommentsOpen($post);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'parent_id' => ['nullable', 'exists:comments,id'],
        ]);

        // Хариу нь зөвхөн мөн энэ мэдээний эх сэтгэгдэлд холбогдоно. Гүн давхаргыг эх рүү нь хавсаргана.
        $parentId = null;
        if (! empty($data['parent_id'])) {
            $parent = Comment::where('post_id', $post->id)->find($data['parent_id']);
            $parentId = $parent?->parent_id ?? $parent?->id;
        }

        // Илэрхий зохисгүй үг агуулсан бол шууд спам болгож, нийтлэгдэхээргүй болгоно.
        $clean = ! $this->hasProfanity($data['body']);

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'parent_id' => $parentId,
            'body' => $data['body'],
            'status' => $clean ? 'pending' : 'spam',
        ]);

        // Зөвхөн хүлээгдэж буй (цэвэр) сэтгэгдлийг л админд мэдэгдэнэ (спам шуугиан үүсгэхгүй).
        if ($clean) {
            $comment->loadMissing('post:id,title');
            Notification::send(User::role('admin')->get(), new NewComment($comment));
        }

        return back()->with('success', 'Сэтгэгдэл хүлээн авлаа. Админ зөвшөөрсний дараа харагдана.');
    }

    /**
     * Илэрхий зохисгүй (ёс бус) үг агуулж буй эсэхийг шалгана. Консерватив жагсаалт.
     */
    private function hasProfanity(string $text): bool
    {
        $words = config('profanity.words', []);
        if (empty($words)) {
            return false;
        }

        // Латин үсгийг жижигрүүлж, кирилл хэвээр; үгсийн хооронд зай/тэмдэгтээр салгаж шалгана.
        $normalized = ' '.mb_strtolower(preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text)).' ';

        foreach ($words as $word) {
            if (mb_strpos($normalized, ' '.mb_strtolower($word).' ') !== false) {
                return true;
            }
        }

        return false;
    }

    /** Like / dislike — дахин дармагц цуцлагдана, эсрэгээ дармагц солигдоно. */
    public function react(Request $request, Comment $comment): RedirectResponse
    {
        $value = (int) $request->validate([
            'value' => ['required', 'in:1,-1'],
        ])['value'];

        abort_unless($comment->status === 'approved', 404);

        $existing = CommentReaction::where('comment_id', $comment->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing && $existing->value === $value) {
            $existing->delete(); // адил товчийг дахин дарвал цуцлана
        } else {
            CommentReaction::updateOrCreate(
                ['comment_id' => $comment->id, 'user_id' => $request->user()->id],
                ['value' => $value],
            );
        }

        $this->recountReactions($comment);

        return back();
    }

    /** Өөрийн сэтгэгдлийг устгах. */
    public function destroy(Request $request, Comment $comment): RedirectResponse
    {
        abort_unless($comment->user_id === $request->user()->id, 403);
        $comment->delete();

        return back()->with('success', 'Сэтгэгдэл устгагдлаа.');
    }

    private function ensureCommentsOpen(Post $post): void
    {
        if (! Setting::boolean('comments_enabled', true) || ! $post->comments_enabled) {
            throw ValidationException::withMessages([
                'body' => 'Энэ мэдээнд сэтгэгдэл бичих боломжгүй байна.',
            ]);
        }
    }

    private function recountReactions(Comment $comment): void
    {
        $counts = CommentReaction::where('comment_id', $comment->id)
            ->selectRaw('coalesce(sum(value = 1), 0) as likes, coalesce(sum(value = -1), 0) as dislikes')
            ->first();

        $comment->update([
            'likes_count' => (int) ($counts->likes ?? 0),
            'dislikes_count' => (int) ($counts->dislikes ?? 0),
        ]);
    }
}
