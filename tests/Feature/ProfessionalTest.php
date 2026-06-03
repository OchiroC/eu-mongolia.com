<?php

namespace Tests\Feature;

use App\Models\Professional;
use App\Models\ProfessionalCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfessionalTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);
        $u = User::factory()->create();
        $u->assignRole('admin');

        return $u;
    }

    private function category(): ProfessionalCategory
    {
        return ProfessionalCategory::firstOrCreate(['slug' => 'huulch'], ['name' => 'Хуульч']);
    }

    private function pro(array $attrs = []): Professional
    {
        return Professional::create(array_merge([
            'user_id' => User::factory()->create()->id,
            'professional_category_id' => $this->category()->id,
            'name' => 'Б. Болд',
            'slug' => 'bold-'.uniqid(),
            'profession' => 'Хуульч',
            'city' => 'Берлин',
            'phone' => '+49 170 000',
            'status' => 'active',
        ], $attrs));
    }

    public function test_public_can_browse_active_professionals(): void
    {
        $this->pro(['name' => 'ACTIVE-PRO']);
        $this->pro(['name' => 'PENDING-PRO', 'status' => 'pending', 'slug' => 'pending-pro']);

        $res = $this->get('/professionals');
        $res->assertOk();
        $res->assertSee('ACTIVE-PRO');
        $res->assertDontSee('PENDING-PRO');
    }

    public function test_pending_professional_detail_is_404(): void
    {
        $p = $this->pro(['status' => 'pending']);
        $this->get("/professionals/{$p->slug}")->assertNotFound();
    }

    public function test_guest_cannot_reveal_contact(): void
    {
        $p = $this->pro();
        $this->post("/professionals/{$p->slug}/reveal")->assertRedirect('/login');
    }

    public function test_logged_in_user_reveals_contact_and_counter_increments(): void
    {
        $p = $this->pro();

        $this->actingAs(User::factory()->create())
            ->post("/professionals/{$p->slug}/reveal")
            ->assertRedirect();

        $this->assertSame(1, $p->fresh()->contact_reveals);
    }

    public function test_user_can_apply_and_profile_is_pending(): void
    {
        $cat = $this->category();
        $user = User::factory()->create();

        $this->actingAs($user)->post('/professionals', [
            'name' => 'Шинэ Мэргэжилтэн',
            'professional_category_id' => $cat->id,
            'profession' => 'Орчуулагч',
            'city' => 'Прага',
            'languages' => ['Монгол', 'Чех'],
            'phone' => '+420 000',
        ])->assertRedirect('/my/professional');

        $this->assertDatabaseHas('professionals', [
            'name' => 'Шинэ Мэргэжилтэн',
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    public function test_user_can_have_only_one_profile(): void
    {
        $user = User::factory()->create();
        $this->pro(['user_id' => $user->id]);

        $this->actingAs($user)->post('/professionals', [
            'name' => 'Хоёр дахь', 'professional_category_id' => $this->category()->id,
        ]);

        $this->assertSame(1, Professional::where('user_id', $user->id)->count());
    }

    public function test_non_owner_cannot_edit(): void
    {
        $p = $this->pro();
        $this->actingAs(User::factory()->create())
            ->get("/professionals/{$p->id}/edit")
            ->assertForbidden();
    }

    public function test_owner_promote_sets_featured(): void
    {
        $user = User::factory()->create();
        $p = $this->pro(['user_id' => $user->id]);

        $this->actingAs($user)->post("/professionals/{$p->id}/promote")->assertRedirect();

        $p->refresh();
        $this->assertTrue($p->is_featured);
        $this->assertTrue($p->isCurrentlyFeatured());
    }

    public function test_admin_can_approve_verify_and_feature(): void
    {
        $admin = $this->admin();
        $p = $this->pro(['status' => 'pending']);

        $this->actingAs($admin)->post("/admin/professionals/{$p->id}/approve")->assertRedirect();
        $this->assertSame('active', $p->fresh()->status);

        $this->actingAs($admin)->post("/admin/professionals/{$p->id}/verify")->assertRedirect();
        $this->assertTrue($p->fresh()->is_verified);

        $this->actingAs($admin)->post("/admin/professionals/{$p->id}/feature")->assertRedirect();
        $this->assertTrue($p->fresh()->is_featured);
    }

    public function test_owner_notified_on_approve(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        $admin = $this->admin();
        $owner = User::factory()->create();
        $p = $this->pro(['user_id' => $owner->id, 'status' => 'pending']);

        $this->actingAs($admin)->post("/admin/professionals/{$p->id}/approve")->assertRedirect();

        \Illuminate\Support\Facades\Notification::assertSentTo($owner, \App\Notifications\ProfessionalStatus::class);
    }

    public function test_non_admin_cannot_access_admin_professionals(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/professionals')
            ->assertForbidden();
    }
}
