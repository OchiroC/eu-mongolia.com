<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\Report;
use App\Models\User;
use App\Notifications\NewReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_marking_read_clears_unread_notifications(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $cat = ListingCategory::firstOrCreate(['slug' => 'electronics'], ['name' => 'Электроник']);
        $listing = Listing::create([
            'user_id' => User::factory()->create()->id,
            'listing_category_id' => $cat->id,
            'title' => 'iPhone', 'slug' => 'iphone-'.uniqid(), 'description' => 'd',
            'price' => 10, 'price_type' => 'fixed', 'city' => 'Berlin', 'status' => 'active',
        ]);
        $report = Report::create([
            'listing_id' => $listing->id, 'reporter_id' => $listing->user_id,
            'reason' => 'spam', 'status' => 'pending',
        ]);

        $admin->notify(new NewReport($report));
        $this->assertSame(1, $admin->unreadNotifications()->count());

        $this->actingAs($admin)->post('/notifications/read')->assertRedirect();

        $this->assertSame(0, $admin->fresh()->unreadNotifications()->count());
    }
}
