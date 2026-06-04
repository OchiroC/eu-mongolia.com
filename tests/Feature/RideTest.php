<?php

namespace Tests\Feature;

use App\Models\Ride;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RideTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);
        $u = User::factory()->create();
        $u->assignRole('admin');

        return $u;
    }

    private function ride(?User $owner = null, array $attrs = []): Ride
    {
        return Ride::create(array_merge([
            'user_id' => ($owner ?? User::factory()->create())->id,
            'from_city' => 'Берлин', 'to_city' => 'Мюнхен',
            'depart_at' => now()->addDays(3), 'seats' => 3, 'price' => '€25',
            'contact_phone' => '+49 170', 'status' => 'active',
        ], $attrs));
    }

    public function test_index_shows_upcoming_active(): void
    {
        $this->ride(null, ['from_city' => 'UPCOMING-CITY']);
        $this->ride(null, ['from_city' => 'PAST-CITY', 'depart_at' => now()->subDays(2)]);

        $res = $this->get('/rides');
        $res->assertOk();
        $res->assertSee('UPCOMING-CITY');
        $res->assertDontSee('PAST-CITY');
    }

    public function test_contact_gated_by_login(): void
    {
        $ride = $this->ride();

        $this->get("/rides/{$ride->id}")->assertOk()->assertDontSee('+49 170');
        $this->actingAs(User::factory()->create())->get("/rides/{$ride->id}")->assertOk()->assertSee('+49 170');
    }

    public function test_user_can_post_ride(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/rides', [
            'from_city' => 'Прага', 'to_city' => 'Вена',
            'depart_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'seats' => 2, 'price' => '€30',
        ])->assertRedirect('/my/rides');

        $this->assertDatabaseHas('rides', ['from_city' => 'Прага', 'user_id' => $user->id]);
    }

    public function test_seats_validation(): void
    {
        $this->actingAs(User::factory()->create())->post('/rides', [
            'from_city' => 'A', 'to_city' => 'B', 'depart_at' => now()->addDay()->format('Y-m-d H:i:s'), 'seats' => 99,
        ])->assertSessionHasErrors('seats');
    }

    public function test_owner_can_close_and_delete(): void
    {
        $owner = User::factory()->create();
        $ride = $this->ride($owner);

        $this->actingAs($owner)->post("/rides/{$ride->id}/close")->assertRedirect();
        $this->assertSame('closed', $ride->fresh()->status);

        $this->actingAs($owner)->delete("/rides/{$ride->id}")->assertRedirect();
        $this->assertDatabaseMissing('rides', ['id' => $ride->id]);
    }

    public function test_non_owner_cannot_edit(): void
    {
        $ride = $this->ride();
        $this->actingAs(User::factory()->create())->get("/rides/{$ride->id}/edit")->assertForbidden();
    }

    public function test_admin_can_manage(): void
    {
        $admin = $this->admin();
        $ride = $this->ride();

        $this->actingAs($admin)->get('/admin/rides')->assertOk();
        $this->actingAs($admin)->delete("/admin/rides/{$ride->id}")->assertRedirect();
        $this->assertDatabaseMissing('rides', ['id' => $ride->id]);
    }

    public function test_non_admin_cannot_access_admin(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/rides')->assertForbidden();
    }
}
