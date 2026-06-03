<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\NewComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    private function makePost(array $attrs = []): Post
    {
        return Post::create(array_merge([
            'title' => 'Мэдээ', 'slug' => 'medee-'.uniqid(), 'body' => 'b',
            'status' => 'published', 'published_at' => now(), 'comments_enabled' => true,
        ], $attrs));
    }

    public function test_guest_cannot_comment(): void
    {
        $post = $this->makePost();
        $this->post("/news/{$post->slug}/comments", ['body' => 'Сайн'])
            ->assertRedirect('/login');
    }

    public function test_comment_is_created_as_pending_and_admin_notified(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $post = $this->makePost();

        $this->actingAs(User::factory()->create())
            ->post("/news/{$post->slug}/comments", ['body' => 'Анхны сэтгэгдэл'])
            ->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'body' => 'Анхны сэтгэгдэл',
            'status' => 'pending',
        ]);
        Notification::assertSentTo($admin, NewComment::class);
    }

    public function test_profane_comment_is_auto_flagged_as_spam(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $post = $this->makePost();

        $this->actingAs(User::factory()->create())
            ->post("/news/{$post->slug}/comments", ['body' => 'Энэ бол новш мэдээ'])
            ->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'body' => 'Энэ бол новш мэдээ',
            'status' => 'spam',
        ]);
        Notification::assertNotSentTo($admin, NewComment::class);
    }

    public function test_cannot_comment_when_post_comments_disabled(): void
    {
        $this->admin();
        $post = $this->makePost(['comments_enabled' => false]);

        $this->actingAs(User::factory()->create())
            ->post("/news/{$post->slug}/comments", ['body' => 'x'])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_cannot_comment_when_globally_disabled(): void
    {
        $this->admin();
        Setting::put('comments_enabled', false);
        $post = $this->makePost();

        $this->actingAs(User::factory()->create())
            ->post("/news/{$post->slug}/comments", ['body' => 'x'])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_reply_attaches_to_root_comment(): void
    {
        $this->admin();
        $post = $this->makePost();
        $root = Comment::create(['post_id' => $post->id, 'user_id' => User::factory()->create()->id, 'body' => 'root', 'status' => 'approved']);

        $this->actingAs(User::factory()->create())
            ->post("/news/{$post->slug}/comments", ['body' => 'хариу', 'parent_id' => $root->id]);

        $this->assertDatabaseHas('comments', ['body' => 'хариу', 'parent_id' => $root->id]);
    }

    public function test_like_and_toggle_off(): void
    {
        $post = $this->makePost();
        $comment = Comment::create(['post_id' => $post->id, 'user_id' => User::factory()->create()->id, 'body' => 'c', 'status' => 'approved']);
        $voter = User::factory()->create();

        $this->actingAs($voter)->post("/comments/{$comment->id}/react", ['value' => 1])->assertRedirect();
        $this->assertSame(1, $comment->fresh()->likes_count);

        // Дахин дарвал цуцлагдана
        $this->actingAs($voter)->post("/comments/{$comment->id}/react", ['value' => 1]);
        $this->assertSame(0, $comment->fresh()->likes_count);
    }

    public function test_like_switches_to_dislike(): void
    {
        $post = $this->makePost();
        $comment = Comment::create(['post_id' => $post->id, 'user_id' => User::factory()->create()->id, 'body' => 'c', 'status' => 'approved']);
        $voter = User::factory()->create();

        $this->actingAs($voter)->post("/comments/{$comment->id}/react", ['value' => 1]);
        $this->actingAs($voter)->post("/comments/{$comment->id}/react", ['value' => -1]);

        $comment->refresh();
        $this->assertSame(0, $comment->likes_count);
        $this->assertSame(1, $comment->dislikes_count);
        $this->assertDatabaseCount('comment_reactions', 1);
    }

    public function test_cannot_react_to_unapproved_comment(): void
    {
        $post = $this->makePost();
        $comment = Comment::create(['post_id' => $post->id, 'user_id' => User::factory()->create()->id, 'body' => 'c', 'status' => 'pending']);

        $this->actingAs(User::factory()->create())
            ->post("/comments/{$comment->id}/react", ['value' => 1])
            ->assertNotFound();
    }

    public function test_owner_can_delete_but_others_cannot(): void
    {
        $post = $this->makePost();
        $owner = User::factory()->create();
        $comment = Comment::create(['post_id' => $post->id, 'user_id' => $owner->id, 'body' => 'c', 'status' => 'approved']);

        $this->actingAs(User::factory()->create())->delete("/comments/{$comment->id}")->assertForbidden();
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);

        $this->actingAs($owner)->delete("/comments/{$comment->id}")->assertRedirect();
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_admin_can_approve_comment(): void
    {
        $admin = $this->admin();
        $post = $this->makePost();
        $comment = Comment::create(['post_id' => $post->id, 'user_id' => User::factory()->create()->id, 'body' => 'c', 'status' => 'pending']);

        $this->actingAs($admin)->post("/admin/comments/{$comment->id}/approve")->assertRedirect();
        $this->assertSame('approved', $comment->fresh()->status);
    }

    public function test_only_approved_comments_appear_on_news_page(): void
    {
        $this->admin();
        $post = $this->makePost();
        Comment::create(['post_id' => $post->id, 'user_id' => User::factory()->create()->id, 'body' => 'APPROVED-ONE', 'status' => 'approved']);
        Comment::create(['post_id' => $post->id, 'user_id' => User::factory()->create()->id, 'body' => 'PENDING-ONE', 'status' => 'pending']);

        $res = $this->get("/news/{$post->slug}");
        $res->assertOk();
        $res->assertSee('APPROVED-ONE');
        $res->assertDontSee('PENDING-ONE');
    }
}
