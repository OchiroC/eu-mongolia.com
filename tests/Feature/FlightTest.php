<?php

namespace Tests\Feature;

use App\Models\Flight;
use App\Models\FlightSchedule;
use App\Models\Parcel;
use App\Models\Ride;
use App\Models\User;
use App\Support\FlightGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FlightTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Лхагва гараг, зуны цаг (UTC+2).
        Carbon::setTestNow(Carbon::parse('2026-09-16 08:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function schedule(array $attrs = []): FlightSchedule
    {
        return FlightSchedule::create(array_merge([
            'code' => 'OM137', 'direction' => 'arrival', 'origin' => 'UBN', 'destination' => 'FRA',
            'weekdays' => [3, 5], 'local_time' => '12:50', 'valid_from' => '2026-05-01', 'valid_to' => null,
        ], $attrs));
    }

    private function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);
        $u = User::factory()->create();
        $u->assignRole('admin');

        return $u;
    }

    public function test_generator_creates_flights_on_schedule_days_in_local_time(): void
    {
        $this->schedule();
        $created = app(FlightGenerator::class)->generate(1);

        // 7 хоногийн дотор Лхагва (16), Баасан (18), Лхагва (23) гараг.
        $this->assertSame(3, $created);
        $first = Flight::orderBy('scheduled_at')->first();
        $this->assertSame('om137-2026-09-16', $first->slug);
        $this->assertSame('12:50', $first->localTime()->format('H:i'));
        $this->assertSame('10:50', $first->scheduled_at->format('H:i')); // UTC
    }

    public function test_generator_is_idempotent_and_keeps_manual_changes(): void
    {
        $this->schedule();
        $gen = app(FlightGenerator::class);
        $gen->generate(1);
        Flight::first()->update(['status' => 'delayed', 'scheduled_at' => now()->addDays(3)]);

        $this->assertSame(0, $gen->generate(1));
        $this->assertSame('delayed', Flight::orderBy('id')->first()->status);
    }

    public function test_generator_respects_valid_to(): void
    {
        $this->schedule(['valid_to' => '2026-09-17']);
        $this->assertSame(1, app(FlightGenerator::class)->generate(4));
    }

    public function test_board_page_lists_flights(): void
    {
        $this->schedule();
        app(FlightGenerator::class)->generate(1);

        $this->get('/flights')->assertOk()->assertInertia(fn (Assert $p) => $p
            ->component('Flights/Index')
            ->has('arrivals', 3)
            ->where('arrivals.0.time', '12:50')
        );
    }

    public function test_user_can_toggle_boarding_and_names_are_hidden_from_guests(): void
    {
        $this->schedule();
        app(FlightGenerator::class)->generate(1);
        $flight = Flight::first();
        $user = User::factory()->create(['name' => 'Болд']);

        $this->actingAs($user)->post("/flights/{$flight->slug}/board")->assertRedirect();
        $this->assertTrue($flight->passengers()->whereKey($user->id)->exists());

        $this->actingAs($user)->get("/flights/{$flight->slug}")->assertInertia(fn (Assert $p) => $p
            ->where('onBoard', true)
            ->where('passengers.0.name', 'Болд')
        );

        auth()->logout();
        $this->get("/flights/{$flight->slug}")->assertInertia(fn (Assert $p) => $p
            ->where('passengers', [])
            ->where('flight.passengers_count', 1)
        );

        $this->actingAs($user)->post("/flights/{$flight->slug}/board");
        $this->assertFalse($flight->passengers()->whereKey($user->id)->exists());
    }

    public function test_cannot_board_cancelled_flight(): void
    {
        $this->schedule();
        app(FlightGenerator::class)->generate(1);
        $flight = Flight::first();
        $flight->update(['status' => 'cancelled']);

        $this->actingAs(User::factory()->create())->post("/flights/{$flight->slug}/board")->assertStatus(422);
    }

    public function test_guest_cannot_board(): void
    {
        $this->schedule();
        app(FlightGenerator::class)->generate(1);

        $this->post('/flights/'.Flight::first()->slug.'/board')->assertRedirect('/login');
    }

    public function test_flight_page_shows_linked_rides_and_parcels(): void
    {
        $this->schedule();
        app(FlightGenerator::class)->generate(1);
        $flight = Flight::first();
        $user = User::factory()->create();
        Ride::create(['user_id' => $user->id, 'flight_id' => $flight->id, 'from_city' => 'Франкфурт нисэх буудал', 'to_city' => 'Майнц', 'depart_at' => now()->addDay(), 'seats' => 2, 'status' => 'active']);
        Parcel::create(['user_id' => $user->id, 'flight_id' => $flight->id, 'type' => 'offer', 'direction' => 'to_germany', 'from_city' => 'УБ', 'to_city' => 'Франкфурт', 'description' => 'Сул зай 5 кг', 'status' => 'active']);

        $this->get("/flights/{$flight->slug}")->assertInertia(fn (Assert $p) => $p
            ->has('rides', 1)
            ->has('parcels', 1)
            ->where('rides.0.to_city', 'Майнц')
        );
    }

    public function test_ride_form_is_prefilled_from_arrival_flight(): void
    {
        $this->schedule();
        app(FlightGenerator::class)->generate(1);
        $flight = Flight::first();

        $this->actingAs(User::factory()->create())->get("/rides/new?flight={$flight->slug}")
            ->assertInertia(fn (Assert $p) => $p
                ->where('preset.flight_id', $flight->id)
                ->where('preset.from_city', 'Франкфурт нисэх буудал')
                ->where('preset.depart_at', '2026-09-16T13:50')
            );
    }

    public function test_admin_can_manage_schedule_and_flight(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/flights/schedules', [
            'code' => 'om138', 'direction' => 'departure', 'origin' => 'fra', 'destination' => 'ubn',
            'weekdays' => [3], 'local_time' => '14:20', 'valid_from' => '2026-09-01',
        ])->assertRedirect();

        $schedule = FlightSchedule::first();
        $this->assertSame('OM138', $schedule->code);
        $this->assertSame('FRA', $schedule->origin);
        $this->assertGreaterThan(0, Flight::count());

        $flight = Flight::first();
        $this->actingAs($admin)->put("/admin/flights/{$flight->slug}", [
            'status' => 'delayed', 'local' => '2026-09-16T16:20', 'note' => '2 цаг хойшилсон',
        ])->assertRedirect();
        $this->assertSame('delayed', $flight->fresh()->status);
        $this->assertSame('16:20', $flight->fresh()->localTime()->format('H:i'));
    }

    public function test_non_admin_cannot_manage_schedule(): void
    {
        $this->actingAs(User::factory()->create())->post('/admin/flights/schedules', [])->assertForbidden();
    }
}
