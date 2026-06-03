<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    private function publishedEvent(): Event
    {
        $event = Event::create([
            'title' => 'Тест эвент', 'slug' => 'test-event',
            'starts_at' => now()->addWeek(), 'status' => 'published',
        ]);
        $event->ticketTypes()->create(['name' => 'Энгийн', 'price' => 10, 'quantity' => 5]);

        return $event;
    }

    public function test_admin_can_create_event_with_ticket_types(): void
    {
        $this->actingAs($this->admin())->post('/admin/events', [
            'title' => 'Шинэ эвент',
            'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'status' => 'published',
            'has_tickets' => true,
            'ticket_types' => [
                ['name' => 'Энгийн', 'price' => 20, 'quantity' => 100],
                ['name' => 'VIP', 'price' => 50, 'quantity' => 20],
            ],
        ])->assertRedirect('/admin/events');

        $this->assertDatabaseHas('events', ['title' => 'Шинэ эвент']);
        $this->assertDatabaseCount('ticket_types', 2);
    }

    public function test_info_event_has_no_ticket_types(): void
    {
        $this->actingAs($this->admin())->post('/admin/events', [
            'title' => 'Мэдээллийн эвент',
            'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'status' => 'published',
            'has_tickets' => false,
            'ticket_types' => [
                ['name' => 'Энгийн', 'price' => 20, 'quantity' => 100],
            ],
        ])->assertRedirect('/admin/events');

        $this->assertDatabaseHas('events', ['title' => 'Мэдээллийн эвент', 'has_tickets' => false]);
        $this->assertDatabaseCount('ticket_types', 0);
    }

    public function test_checkout_blocked_on_info_event(): void
    {
        $event = Event::create([
            'title' => 'Info', 'slug' => 'info-event',
            'starts_at' => now()->addWeek(), 'status' => 'published', 'has_tickets' => false,
        ]);

        $this->actingAs(User::factory()->create())
            ->post("/events/{$event->slug}/checkout", [
                'items' => [['ticket_type_id' => 1, 'quantity' => 1]],
                'buyer_name' => 'Бат', 'buyer_email' => 'bat@example.com',
            ])->assertNotFound();
    }

    public function test_admin_can_view_event_sales_report(): void
    {
        $event = $this->publishedEvent();
        $type = $event->ticketTypes->first();
        $buyer = User::factory()->create();

        // Нэг захиалга үүсгэнэ (sold нэмэгдэнэ).
        $this->actingAs($buyer)->post("/events/{$event->slug}/checkout", [
            'items' => [['ticket_type_id' => $type->id, 'quantity' => 2]],
            'buyer_name' => 'Бат', 'buyer_email' => 'bat@example.com',
        ]);

        $this->actingAs($this->admin())
            ->get("/admin/events/{$event->id}/sales")
            ->assertOk();

        $this->assertSame(2, $type->fresh()->sold);
    }

    public function test_non_admin_cannot_view_sales(): void
    {
        $event = $this->publishedEvent();
        $this->actingAs(User::factory()->create())
            ->get("/admin/events/{$event->id}/sales")
            ->assertForbidden();
    }

    public function test_checkout_creates_pending_order_and_tickets(): void
    {
        $event = $this->publishedEvent();
        $type = $event->ticketTypes->first();
        $user = User::factory()->create();

        $this->actingAs($user)->post("/events/{$event->slug}/checkout", [
            'items' => [['ticket_type_id' => $type->id, 'quantity' => 2]],
            'buyer_name' => 'Бат', 'buyer_email' => 'bat@example.com',
        ])->assertRedirect();

        $order = Order::first();
        $this->assertSame('pending', $order->status);
        $this->assertEquals(20, $order->total);
        $this->assertCount(2, $order->tickets);
        $this->assertSame(2, $type->fresh()->sold);
    }

    public function test_cannot_overbook_tickets(): void
    {
        $event = $this->publishedEvent();
        $type = $event->ticketTypes->first(); // quantity 5
        $user = User::factory()->create();

        $this->actingAs($user)->post("/events/{$event->slug}/checkout", [
            'items' => [['ticket_type_id' => $type->id, 'quantity' => 10]],
            'buyer_name' => 'Бат', 'buyer_email' => 'bat@example.com',
        ])->assertStatus(422);

        $this->assertSame(0, $type->fresh()->sold);
    }

    public function test_mock_payment_marks_order_paid(): void
    {
        $event = $this->publishedEvent();
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id, 'event_id' => $event->id,
            'reference' => 'ORD-TEST1', 'total' => 10, 'status' => 'pending',
        ]);

        $this->actingAs($user)->post("/orders/{$order->id}/pay")->assertRedirect();
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->paid_at);
    }

    public function test_user_cannot_view_others_order(): void
    {
        $event = $this->publishedEvent();
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $order = Order::create([
            'user_id' => $owner->id, 'event_id' => $event->id,
            'reference' => 'ORD-TEST2', 'total' => 10, 'status' => 'paid',
        ]);

        $this->actingAs($other)->get("/orders/{$order->id}")->assertForbidden();
    }

    public function test_check_in_marks_ticket_used(): void
    {
        $event = $this->publishedEvent();
        $type = $event->ticketTypes->first();
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id, 'event_id' => $event->id,
            'reference' => 'ORD-TEST3', 'total' => 10, 'status' => 'paid',
        ]);
        $ticket = $order->tickets()->create([
            'ticket_type_id' => $type->id, 'code' => 'CODE-123', 'status' => 'valid',
        ]);

        $this->actingAs($this->admin())->post('/admin/check-in', ['code' => 'CODE-123'])->assertRedirect();
        $this->assertSame('used', $ticket->fresh()->status);

        $this->actingAs($this->admin())->post('/admin/check-in', ['code' => 'CODE-123'])->assertRedirect();
        $this->assertSame('used', $ticket->fresh()->status);
    }
}
