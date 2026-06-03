<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\Report;
use App\Models\User;
use App\Notifications\NewReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportModerationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    private function listing(array $attrs = []): Listing
    {
        $cat = ListingCategory::firstOrCreate(['slug' => 'electronics'], ['name' => 'Электроник']);

        return Listing::create(array_merge([
            'user_id' => User::factory()->create()->id,
            'listing_category_id' => $cat->id,
            'title' => 'iPhone',
            'slug' => 'iphone-'.uniqid(),
            'description' => 'Сайн утас',
            'price' => 400,
            'price_type' => 'fixed',
            'city' => 'Berlin',
            'status' => 'active',
        ], $attrs));
    }

    public function test_guest_cannot_report(): void
    {
        $listing = $this->listing();
        $this->post("/zar/{$listing->id}/report", ['reason' => 'spam'])
            ->assertRedirect('/login');
    }

    public function test_user_can_report_and_admins_are_notified(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $listing = $this->listing();
        $reporter = User::factory()->create();

        $this->actingAs($reporter)
            ->post("/zar/{$listing->id}/report", ['reason' => 'scam', 'note' => 'Луйвар'])
            ->assertRedirect();

        $this->assertDatabaseHas('reports', [
            'listing_id' => $listing->id,
            'reporter_id' => $reporter->id,
            'reason' => 'scam',
            'status' => 'pending',
        ]);

        Notification::assertSentTo($admin, NewReport::class);
    }

    public function test_duplicate_pending_report_is_prevented(): void
    {
        $this->admin();
        $listing = $this->listing();
        $reporter = User::factory()->create();

        $this->actingAs($reporter)->post("/zar/{$listing->id}/report", ['reason' => 'spam']);
        $this->actingAs($reporter)->post("/zar/{$listing->id}/report", ['reason' => 'spam']);

        $this->assertDatabaseCount('reports', 1);
    }

    public function test_non_admin_cannot_view_reports(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/reports')
            ->assertForbidden();
    }

    public function test_admin_can_hide_reported_listing_and_owner_notified(): void
    {
        Notification::fake();
        $admin = $this->admin();
        $listing = $this->listing();
        $report = Report::create([
            'listing_id' => $listing->id,
            'reporter_id' => User::factory()->create()->id,
            'reason' => 'prohibited',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post("/admin/reports/{$report->id}/hide")->assertRedirect();

        $this->assertSame('inactive', $listing->fresh()->status);
        $this->assertSame('actioned', $report->fresh()->status);
        Notification::assertSentTo($listing->user, \App\Notifications\ListingModerated::class);
    }

    public function test_admin_can_dismiss_report(): void
    {
        $admin = $this->admin();
        $listing = $this->listing();
        $report = Report::create([
            'listing_id' => $listing->id,
            'reporter_id' => User::factory()->create()->id,
            'reason' => 'other',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post("/admin/reports/{$report->id}/dismiss")->assertRedirect();

        $this->assertSame('dismissed', $report->fresh()->status);
        $this->assertSame('active', $listing->fresh()->status);
    }

    public function test_admin_can_destroy_reported_listing(): void
    {
        $admin = $this->admin();
        $listing = $this->listing();
        $report = Report::create([
            'listing_id' => $listing->id,
            'reporter_id' => User::factory()->create()->id,
            'reason' => 'scam',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->delete("/admin/reports/{$report->id}/listing")->assertRedirect();

        // Зар устгагдсан; гомдол нь шийдэгдсэн (эсвэл cascade-аар устсан) — аль ч тохиолдолд pending үлдээгүй.
        $this->assertDatabaseMissing('listings', ['id' => $listing->id]);
        $this->assertDatabaseMissing('reports', ['id' => $report->id, 'status' => 'pending']);
    }
}
