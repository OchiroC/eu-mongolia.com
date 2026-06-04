<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BusinessTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);
        $u = User::factory()->create();
        $u->assignRole('admin');

        return $u;
    }

    private function biz(?User $owner = null, array $attrs = []): Business
    {
        return Business::create(array_merge([
            'user_id' => ($owner ?? User::factory()->create())->id,
            'name' => 'Бизнес', 'slug' => 'b-'.uniqid(), 'category' => 'restaurant',
            'city' => 'Берлин', 'country' => 'Герман', 'status' => 'active',
        ], $attrs));
    }

    public function test_public_sees_active_only(): void
    {
        $this->biz(null, ['name' => 'ACTIVE-BIZ']);
        $this->biz(null, ['name' => 'PENDING-BIZ', 'status' => 'pending', 'slug' => 'pending-biz']);

        $res = $this->get('/businesses');
        $res->assertOk();
        $res->assertSee('ACTIVE-BIZ');
        $res->assertDontSee('PENDING-BIZ');
    }

    public function test_pending_detail_404_for_public(): void
    {
        $b = $this->biz(null, ['status' => 'pending']);
        $this->get("/businesses/{$b->slug}")->assertNotFound();
    }

    public function test_user_can_submit_pending(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/businesses', [
            'name' => 'Шинэ ресторан', 'category' => 'restaurant', 'city' => 'Прага',
        ])->assertRedirect('/my/businesses');

        $this->assertDatabaseHas('businesses', ['name' => 'Шинэ ресторан', 'user_id' => $user->id, 'status' => 'pending']);
    }

    public function test_invalid_category_rejected(): void
    {
        $this->actingAs(User::factory()->create())->post('/businesses', [
            'name' => 'X', 'category' => 'bogus', 'city' => 'A',
        ])->assertSessionHasErrors('category');
    }

    public function test_owner_promote_and_delete(): void
    {
        $owner = User::factory()->create();
        $b = $this->biz($owner);

        $this->actingAs($owner)->post("/businesses/{$b->id}/promote")->assertRedirect();
        $this->assertTrue($b->fresh()->isCurrentlyFeatured());

        $this->actingAs($owner)->delete("/businesses/{$b->id}")->assertRedirect();
        $this->assertDatabaseMissing('businesses', ['id' => $b->id]);
    }

    public function test_non_owner_cannot_edit(): void
    {
        $b = $this->biz();
        $this->actingAs(User::factory()->create())->get("/businesses/{$b->id}/edit")->assertForbidden();
    }

    public function test_admin_approve_feature_delete(): void
    {
        $admin = $this->admin();
        $b = $this->biz(null, ['status' => 'pending']);

        $this->actingAs($admin)->post("/admin/businesses/{$b->id}/approve")->assertRedirect();
        $this->assertSame('active', $b->fresh()->status);

        $this->actingAs($admin)->post("/admin/businesses/{$b->id}/feature")->assertRedirect();
        $this->assertTrue($b->fresh()->is_featured);

        $this->actingAs($admin)->delete("/admin/businesses/{$b->id}")->assertRedirect();
        $this->assertDatabaseMissing('businesses', ['id' => $b->id]);
    }

    public function test_non_admin_cannot_access_admin(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/businesses')->assertForbidden();
    }
}
