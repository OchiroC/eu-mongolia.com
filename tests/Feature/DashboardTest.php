<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_regular_user_sees_personal_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Dashboard'));
    }

    public function test_admin_sees_admin_dashboard_with_stats(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $event = Event::create([
            'title' => 'E', 'slug' => 'e', 'starts_at' => now()->addWeek(), 'status' => 'published',
        ]);
        Order::create([
            'user_id' => $admin->id, 'event_id' => $event->id,
            'reference' => 'ORD-X', 'total' => 50, 'status' => 'paid', 'paid_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Dashboard')
                ->where('stats.revenue', 50)
                ->where('stats.orders_paid', 1)
            );
    }
}
