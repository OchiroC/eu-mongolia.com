<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['admin', 'editor', 'moderator', 'organizer', 'advertiser', 'user'] as $r) {
            Role::firstOrCreate(['name' => $r]);
        }
    }

    private function userWith(string $role): User
    {
        $u = User::factory()->create();
        $u->assignRole($role);

        return $u;
    }

    // ── Ажилтны CRUD ─────────────────────────────────────────────
    public function test_admin_can_create_staff_user(): void
    {
        $admin = $this->userWith('admin');

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Сэтгүүлч Бат',
            'email' => 'bat@eu-mongolia.test',
            'password' => 'password1234',
            'role' => 'editor',
        ])->assertRedirect();

        $user = User::where('email', 'bat@eu-mongolia.test')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('editor'));
    }

    public function test_cannot_create_user_with_admin_role(): void
    {
        $admin = $this->userWith('admin');

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'X', 'email' => 'x@x.test', 'password' => 'password1234', 'role' => 'admin',
        ])->assertSessionHasErrors('role');
    }

    public function test_admin_can_update_and_delete_staff(): void
    {
        $admin = $this->userWith('admin');
        $staff = $this->userWith('editor');

        $this->actingAs($admin)->put("/admin/users/{$staff->id}", [
            'name' => 'Шинэчилсэн', 'email' => $staff->email, 'role' => 'moderator',
        ])->assertRedirect();
        $this->assertTrue($staff->fresh()->hasRole('moderator'));
        $this->assertFalse($staff->fresh()->hasRole('editor'));

        $this->actingAs($admin)->delete("/admin/users/{$staff->id}")->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    }

    // ── Admin хамгаалалт ─────────────────────────────────────────
    public function test_admin_cannot_be_edited_deleted_or_blocked(): void
    {
        $admin = $this->userWith('admin');
        $other = $this->userWith('admin');

        $this->actingAs($admin)->put("/admin/users/{$other->id}", [
            'name' => 'Hack', 'email' => $other->email, 'role' => 'user',
        ])->assertForbidden();

        $this->actingAs($admin)->delete("/admin/users/{$other->id}")->assertForbidden();
        $this->actingAs($admin)->post("/admin/users/{$other->id}/toggle-block")->assertForbidden();

        // Өөрийгөө ч устгаж/блоклож болохгүй (admin тул)
        $this->actingAs($admin)->delete("/admin/users/{$admin->id}")->assertForbidden();
    }

    public function test_admin_can_block_non_admin(): void
    {
        $admin = $this->userWith('admin');
        $staff = $this->userWith('editor');

        $this->actingAs($admin)->post("/admin/users/{$staff->id}/toggle-block")->assertRedirect();
        $this->assertNotNull($staff->fresh()->blocked_at);
    }

    // ── Хэсгийн хандалт (RBAC) ───────────────────────────────────
    public function test_editor_accesses_posts_not_users_or_comments(): void
    {
        $editor = $this->userWith('editor');

        $this->actingAs($editor)->get('/admin/posts')->assertOk();
        $this->actingAs($editor)->get('/admin/guides')->assertOk();
        $this->actingAs($editor)->get('/admin/users')->assertForbidden();
        $this->actingAs($editor)->get('/admin/comments')->assertForbidden();
        $this->actingAs($editor)->get('/admin/settings')->assertForbidden();
    }

    public function test_moderator_accesses_comments_not_posts(): void
    {
        $mod = $this->userWith('moderator');

        $this->actingAs($mod)->get('/admin/comments')->assertOk();
        $this->actingAs($mod)->get('/admin/reports')->assertOk();
        $this->actingAs($mod)->get('/admin/posts')->assertForbidden();
        $this->actingAs($mod)->get('/admin/users')->assertForbidden();
    }

    public function test_editor_redirected_from_admin_dashboard(): void
    {
        $editor = $this->userWith('editor');
        $this->actingAs($editor)->get('/admin')->assertRedirect('/admin/posts');
    }

    public function test_plain_user_cannot_enter_admin(): void
    {
        $u = $this->userWith('user');
        $this->actingAs($u)->get('/admin')->assertForbidden();
        $this->actingAs($u)->get('/admin/posts')->assertForbidden();
    }
}
