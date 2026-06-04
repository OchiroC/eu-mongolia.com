<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use App\Notifications\NewAnswer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuestionTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);
        $u = User::factory()->create();
        $u->assignRole('admin');

        return $u;
    }

    private function question(?User $owner = null, array $attrs = []): Question
    {
        return Question::create(array_merge([
            'user_id' => ($owner ?? User::factory()->create())->id,
            'title' => 'Асуулт', 'slug' => 'q-'.uniqid(), 'body' => 'тайлбар',
            'category' => 'visa',
        ], $attrs));
    }

    public function test_guest_can_browse_and_view(): void
    {
        $q = $this->question(null, ['title' => 'BROWSE-Q']);
        $this->get('/questions')->assertOk()->assertSee('BROWSE-Q');
        $this->get("/questions/{$q->slug}")->assertOk();
    }

    public function test_user_can_ask(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/questions', [
            'title' => 'Шинэ асуулт', 'body' => 'тайлбар', 'category' => 'work',
        ])->assertRedirect();

        $this->assertDatabaseHas('questions', ['title' => 'Шинэ асуулт', 'user_id' => $user->id]);
    }

    public function test_invalid_category_rejected(): void
    {
        $this->actingAs(User::factory()->create())->post('/questions', [
            'title' => 'X', 'body' => 'y', 'category' => 'bogus',
        ])->assertSessionHasErrors('category');
    }

    public function test_answer_increments_count_and_notifies_owner(): void
    {
        Notification::fake();
        $owner = User::factory()->create();
        $q = $this->question($owner);

        $this->actingAs(User::factory()->create())
            ->post("/questions/{$q->slug}/answers", ['body' => 'Миний хариулт'])
            ->assertRedirect();

        $this->assertDatabaseHas('answers', ['question_id' => $q->id, 'body' => 'Миний хариулт']);
        $this->assertSame(1, $q->fresh()->answers_count);
        Notification::assertSentTo($owner, NewAnswer::class);
    }

    public function test_answering_own_question_does_not_notify(): void
    {
        Notification::fake();
        $owner = User::factory()->create();
        $q = $this->question($owner);

        $this->actingAs($owner)->post("/questions/{$q->slug}/answers", ['body' => 'өөрийн хариулт']);

        Notification::assertNotSentTo($owner, NewAnswer::class);
    }

    public function test_vote_toggle(): void
    {
        $q = $this->question();
        $a = $q->answers()->create(['user_id' => User::factory()->create()->id, 'body' => 'a']);
        $voter = User::factory()->create();

        $this->actingAs($voter)->post("/answers/{$a->id}/vote");
        $this->assertSame(1, $a->fresh()->votes_count);

        $this->actingAs($voter)->post("/answers/{$a->id}/vote");
        $this->assertSame(0, $a->fresh()->votes_count);
    }

    public function test_owner_accepts_best_answer(): void
    {
        $owner = User::factory()->create();
        $q = $this->question($owner);
        $a = $q->answers()->create(['user_id' => User::factory()->create()->id, 'body' => 'a']);

        $this->actingAs($owner)->post("/answers/{$a->id}/accept")->assertRedirect();
        $this->assertSame($a->id, $q->fresh()->best_answer_id);

        // toggle off
        $this->actingAs($owner)->post("/answers/{$a->id}/accept");
        $this->assertNull($q->fresh()->best_answer_id);
    }

    public function test_non_owner_cannot_accept(): void
    {
        $q = $this->question();
        $a = $q->answers()->create(['user_id' => User::factory()->create()->id, 'body' => 'a']);

        $this->actingAs(User::factory()->create())->post("/answers/{$a->id}/accept")->assertForbidden();
    }

    public function test_admin_can_delete_question(): void
    {
        $admin = $this->admin();
        $q = $this->question();
        $this->actingAs($admin)->delete("/admin/questions/{$q->id}")->assertRedirect();
        $this->assertDatabaseMissing('questions', ['id' => $q->id]);
    }
}
