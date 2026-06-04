<?php

namespace Tests\Feature;

use App\Models\HousingPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HousingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);
        $u = User::factory()->create();
        $u->assignRole('admin');

        return $u;
    }

    private function makePost(?User $owner = null, array $attrs = []): HousingPost
    {
        return HousingPost::create(array_merge([
            'user_id' => ($owner ?? User::factory()->create())->id,
            'title' => 'Өрөө', 'slug' => 'h-'.uniqid(), 'type' => 'room',
            'city' => 'Берлин', 'country' => 'Герман', 'price' => 400,
            'gender_pref' => 'any', 'contact_phone' => '+49 170', 'status' => 'active',
        ], $attrs));
    }

    public function test_public_browse_active_only(): void
    {
        $this->makePost(null, ['title' => 'ACTIVE-ROOM']);
        $this->makePost(null, ['title' => 'CLOSED-ROOM', 'status' => 'closed']);

        $res = $this->get('/housing');
        $res->assertOk();
        $res->assertSee('ACTIVE-ROOM');
        $res->assertDontSee('CLOSED-ROOM');
    }

    public function test_contact_gated_by_login(): void
    {
        $p = $this->makePost();
        $this->get("/housing/{$p->slug}")->assertOk()->assertDontSee('+49 170');
        $this->actingAs(User::factory()->create())->get("/housing/{$p->slug}")->assertOk()->assertSee('+49 170');
    }

    public function test_user_can_post(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/housing', [
            'title' => 'Шинэ өрөө', 'type' => 'room', 'city' => 'Мюнхен',
            'gender_pref' => 'any', 'price' => 500,
        ])->assertRedirect('/my/housing');

        $this->assertDatabaseHas('housing_posts', ['title' => 'Шинэ өрөө', 'user_id' => $user->id]);
    }

    public function test_invalid_type_rejected(): void
    {
        $this->actingAs(User::factory()->create())->post('/housing', [
            'title' => 'X', 'type' => 'bogus', 'city' => 'A', 'gender_pref' => 'any',
        ])->assertSessionHasErrors('type');
    }

    public function test_owner_close_and_delete(): void
    {
        $owner = User::factory()->create();
        $p = $this->makePost($owner);

        $this->actingAs($owner)->post("/housing/{$p->id}/close")->assertRedirect();
        $this->assertSame('closed', $p->fresh()->status);

        $this->actingAs($owner)->delete("/housing/{$p->id}")->assertRedirect();
        $this->assertDatabaseMissing('housing_posts', ['id' => $p->id]);
    }

    public function test_non_owner_cannot_edit(): void
    {
        $p = $this->makePost();
        $this->actingAs(User::factory()->create())->get("/housing/{$p->id}/edit")->assertForbidden();
    }

    public function test_admin_can_manage(): void
    {
        $admin = $this->admin();
        $p = $this->makePost();

        $this->actingAs($admin)->get('/admin/housing')->assertOk();
        $this->actingAs($admin)->delete("/admin/housing/{$p->id}")->assertRedirect();
        $this->assertDatabaseMissing('housing_posts', ['id' => $p->id]);
    }

    public function test_non_admin_cannot_access_admin(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/housing')->assertForbidden();
    }
}
