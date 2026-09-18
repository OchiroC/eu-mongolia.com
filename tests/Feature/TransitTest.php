<?php

namespace Tests\Feature;

use App\Models\Flight;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TransitTest extends TestCase
{
    use RefreshDatabase;

    private function flight(array $attrs = []): Flight
    {
        $at = now()->addDays(3)->setTime(10, 50);

        return Flight::create(array_merge([
            'code' => 'OM137', 'direction' => 'arrival', 'origin' => 'UBN', 'destination' => 'FRA',
            'scheduled_at' => $at, 'status' => 'scheduled', 'slug' => Flight::slugFor('OM137', $at),
        ], $attrs));
    }

    public function test_index_lists_cities_with_traveller_counts(): void
    {
        $flight = $this->flight();
        $flight->passengers()->attach(User::factory()->create()->id, ['destination' => 'berlin']);

        $this->get('/damjih')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Transit/Index')
            ->where('cities.0.slug', 'berlin')
            ->where('cities.0.travellers', 1)
            ->where('cities.1.travellers', 0));
    }

    public function test_city_page_shows_flights_and_hides_names_from_guests(): void
    {
        $flight = $this->flight();
        $flight->passengers()->attach(User::factory()->create(['name' => 'Бат'])->id, ['destination' => 'prague']);

        $this->get('/damjih/prague')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Transit/Show')
            ->where('city.to', 'Прага руу')
            ->where('flights.0.travellers', 1)
            ->where('flights.0.names', []));

        $this->actingAs(User::factory()->create())->get('/damjih/prague')
            ->assertInertia(fn (Assert $page) => $page->where('flights.0.names', ['Бат']));
    }

    public function test_unknown_city_is_404(): void
    {
        $this->get('/damjih/atlantis')->assertNotFound();
    }

    public function test_boarding_saves_destination_and_it_can_be_changed(): void
    {
        $flight = $this->flight();
        $user = User::factory()->create();

        $this->actingAs($user)->post("/flights/{$flight->slug}/board", ['destination' => 'berlin'])->assertRedirect();
        $this->assertSame('berlin', $flight->passengers()->first()->pivot->destination);

        $this->actingAs($user)->put("/flights/{$flight->slug}/board", ['destination' => 'paris'])->assertRedirect();
        $this->assertSame('paris', $flight->passengers()->first()->pivot->destination);

        $this->actingAs($user)->put("/flights/{$flight->slug}/board", ['destination' => 'mars'])->assertSessionHasErrors('destination');

        // Дахин дарахад жагсаалтаас хасагдана.
        $this->actingAs($user)->post("/flights/{$flight->slug}/board")->assertRedirect();
        $this->assertSame(0, $flight->passengers()->count());
    }

    public function test_departure_flights_ignore_destination(): void
    {
        $flight = $this->flight(['direction' => 'departure', 'origin' => 'FRA', 'destination' => 'UBN', 'slug' => 'om138-test']);
        $user = User::factory()->create();

        $this->actingAs($user)->post("/flights/{$flight->slug}/board", ['destination' => 'berlin'])->assertRedirect();
        $this->assertNull($flight->passengers()->first()->pivot->destination);
    }

    public function test_share_image_is_png_and_linked_in_seo(): void
    {
        $flight = $this->flight();

        $response = $this->get("/flights/{$flight->slug}/share.png")->assertOk()->assertHeader('Content-Type', 'image/png');
        [$w, $h] = getimagesizefromstring($response->getContent());
        $this->assertSame([1200, 630], [$w, $h]);

        $this->get("/flights/{$flight->slug}")->assertInertia(fn (Assert $page) => $page
            ->where('seo.image', fn ($url) => str_contains($url, "/flights/{$flight->slug}/share.png?v=")));
    }
}
