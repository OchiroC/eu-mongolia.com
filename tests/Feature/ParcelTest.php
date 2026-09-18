<?php

namespace Tests\Feature;

use App\Models\Guide;
use App\Models\Parcel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ParcelTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $attrs = []): array
    {
        return array_merge([
            'type' => 'offer', 'direction' => 'to_germany', 'travel_date' => now()->addDays(5)->toDateString(),
            'from_city' => 'Улаанбаатар', 'to_city' => 'Франкфурт', 'weight_kg' => 5,
            'price' => '5 €/кг', 'description' => 'Ачаанд 5 кг сул зай байна.', 'contact_phone' => '+49 111',
        ], $attrs);
    }

    public function test_user_can_create_parcel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/achaa', $this->payload())->assertRedirect();

        $this->assertDatabaseHas('parcels', ['user_id' => $user->id, 'to_city' => 'Франкфурт', 'status' => 'active']);
    }

    public function test_date_or_flight_is_required(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/achaa', $this->payload(['travel_date' => null]))
            ->assertSessionHasErrors('travel_date');
    }

    public function test_guest_cannot_create(): void
    {
        $this->post('/achaa', $this->payload())->assertRedirect('/login');
    }

    public function test_phone_is_hidden_from_guests(): void
    {
        $owner = User::factory()->create();
        $parcel = Parcel::create($this->payload() + ['user_id' => $owner->id, 'status' => 'active']);

        $this->get("/achaa/{$parcel->id}")->assertInertia(fn (Assert $p) => $p->where('parcel.contact_phone', null));
        $this->actingAs(User::factory()->create())->get("/achaa/{$parcel->id}")
            ->assertInertia(fn (Assert $p) => $p->where('parcel.contact_phone', '+49 111'));
    }

    public function test_only_owner_can_edit_close_delete(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $parcel = Parcel::create($this->payload() + ['user_id' => $owner->id, 'status' => 'active']);

        $this->actingAs($other)->put("/achaa/{$parcel->id}", $this->payload(['to_city' => 'X']))->assertForbidden();
        $this->actingAs($other)->post("/achaa/{$parcel->id}/close")->assertForbidden();
        $this->actingAs($other)->delete("/achaa/{$parcel->id}")->assertForbidden();

        $this->actingAs($owner)->post("/achaa/{$parcel->id}/close")->assertRedirect();
        $this->assertSame('closed', $parcel->fresh()->status);
    }

    public function test_closed_parcel_is_hidden_from_public(): void
    {
        $parcel = Parcel::create($this->payload() + ['user_id' => User::factory()->create()->id, 'status' => 'closed']);

        $this->get("/achaa/{$parcel->id}")->assertNotFound();
        $this->get('/achaa')->assertInertia(fn (Assert $p) => $p->has('parcels.data', 0));
    }

    public function test_index_filters_by_type(): void
    {
        $u = User::factory()->create();
        Parcel::create($this->payload() + ['user_id' => $u->id, 'status' => 'active']);
        Parcel::create($this->payload(['type' => 'request']) + ['user_id' => $u->id, 'status' => 'active']);

        $this->get('/achaa?type=request')->assertInertia(fn (Assert $p) => $p
            ->has('parcels.data', 1)
            ->where('parcels.data.0.type', 'request')
        );
    }

    public function test_journey_toggle_marks_step(): void
    {
        $user = User::factory()->create();
        $guide = Guide::create([
            'title' => 'Алхам', 'slug' => 'alxam', 'body' => '<p>b</p>', 'topic' => 'visa',
            'stage' => 'before', 'status' => 'published', 'published_at' => now(),
        ]);

        $this->actingAs($user)->post('/journey/alxam')->assertRedirect();
        $this->assertTrue($user->journeyGuides()->whereKey($guide->id)->exists());

        $this->actingAs($user)->get('/')->assertInertia(fn (Assert $p) => $p->where('journeyDone', ['alxam']));

        $this->actingAs($user)->post('/journey/alxam');
        $this->assertFalse($user->journeyGuides()->whereKey($guide->id)->exists());
    }

    public function test_arrival_kit_and_map_render(): void
    {
        $this->get('/ireh')->assertOk()->assertSee('Ирэх өдрийн багц')->assertSee('Терминал 3')->assertSee('110');
        $this->get('/map')->assertOk()->assertInertia(fn (Assert $p) => $p->component('Map/Index')->has('places', 0));
    }
}
