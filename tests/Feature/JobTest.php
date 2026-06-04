<?php

namespace Tests\Feature;

use App\Models\JobPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class JobTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);
        $u = User::factory()->create();
        $u->assignRole('admin');

        return $u;
    }

    private function job(?User $owner = null, array $attrs = []): JobPost
    {
        return JobPost::create(array_merge([
            'user_id' => ($owner ?? User::factory()->create())->id,
            'title' => 'Ажилтан', 'slug' => 'j-'.uniqid(), 'description' => 'd',
            'employment_type' => 'full_time', 'category' => 'service',
            'city' => 'Berlin', 'country' => 'Герман', 'status' => 'active',
            'contact_email' => 'job@example.com', 'contact_phone' => '+49 170',
        ], $attrs));
    }

    public function test_public_browse_active_only(): void
    {
        $this->job(null, ['title' => 'ACTIVE-JOB']);
        $this->job(null, ['title' => 'CLOSED-JOB', 'status' => 'closed']);

        $res = $this->get('/jobs');
        $res->assertOk();
        $res->assertSee('ACTIVE-JOB');
        $res->assertDontSee('CLOSED-JOB');
    }

    public function test_contact_hidden_from_guests_shown_to_users(): void
    {
        $job = $this->job();

        // Зочин — холбоо барих утас харагдахгүй
        $this->get("/jobs/{$job->slug}")->assertOk()->assertDontSee('job@example.com');

        // Нэвтэрсэн — харагдана
        $this->actingAs(User::factory()->create())
            ->get("/jobs/{$job->slug}")
            ->assertOk()
            ->assertSee('job@example.com');
    }

    public function test_user_can_post_job(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/jobs', [
            'title' => 'Шинэ ажил',
            'description' => 'Тодорхойлолт',
            'employment_type' => 'part_time',
            'category' => 'cleaning',
            'city' => 'Прага',
            'contact_email' => 'a@b.com',
        ])->assertRedirect('/my/jobs');

        $this->assertDatabaseHas('job_posts', ['title' => 'Шинэ ажил', 'user_id' => $user->id, 'status' => 'active']);
    }

    public function test_invalid_category_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/jobs', [
            'title' => 'X', 'description' => 'd', 'employment_type' => 'full_time', 'category' => 'bogus',
        ])->assertSessionHasErrors('category');
    }

    public function test_owner_can_close_and_delete(): void
    {
        $owner = User::factory()->create();
        $job = $this->job($owner);

        $this->actingAs($owner)->post("/jobs/{$job->id}/close")->assertRedirect();
        $this->assertSame('closed', $job->fresh()->status);

        $this->actingAs($owner)->delete("/jobs/{$job->id}")->assertRedirect();
        $this->assertDatabaseMissing('job_posts', ['id' => $job->id]);
    }

    public function test_non_owner_cannot_edit(): void
    {
        $job = $this->job();
        $this->actingAs(User::factory()->create())->get("/jobs/{$job->id}/edit")->assertForbidden();
    }

    public function test_admin_can_manage(): void
    {
        $admin = $this->admin();
        $job = $this->job();

        $this->actingAs($admin)->get('/admin/jobs')->assertOk();
        $this->actingAs($admin)->delete("/admin/jobs/{$job->id}")->assertRedirect();
        $this->assertDatabaseMissing('job_posts', ['id' => $job->id]);
    }

    public function test_non_admin_cannot_access_admin(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/jobs')->assertForbidden();
    }
}
