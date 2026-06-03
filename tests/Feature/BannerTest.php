<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BannerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_admin_can_create_banner(): void
    {
        $this->actingAs($this->admin())->post('/admin/banners', [
            'title' => 'Тест баннер',
            'image_path' => 'https://example.com/b.jpg',
            'link_url' => 'https://example.com',
            'placement' => 'home_top',
            'status' => 'pending',
            'price' => 50,
            'is_paid' => false,
        ])->assertRedirect('/admin/banners');

        $this->assertDatabaseHas('banners', ['title' => 'Тест баннер', 'is_paid' => false]);
    }

    public function test_mock_payment_activates_banner(): void
    {
        $banner = Banner::create([
            'title' => 'B', 'image_path' => 'x', 'placement' => 'home_top',
            'status' => 'pending', 'price' => 10, 'is_paid' => false,
        ]);

        $this->actingAs($this->admin())->post("/admin/banners/{$banner->id}/pay");

        $banner->refresh();
        $this->assertTrue($banner->is_paid);
        $this->assertSame('active', $banner->status);
    }

    public function test_click_increments_and_redirects(): void
    {
        $banner = Banner::create([
            'title' => 'B', 'image_path' => 'x', 'link_url' => 'https://example.com',
            'placement' => 'home_top', 'status' => 'active', 'is_paid' => true,
        ]);

        $this->get("/banners/{$banner->id}/click")->assertRedirect('https://example.com');
        $this->assertSame(1, $banner->fresh()->clicks);
    }

    public function test_impression_increments_without_csrf(): void
    {
        $banner = Banner::create([
            'title' => 'B', 'image_path' => 'x', 'placement' => 'home_top',
            'status' => 'active', 'is_paid' => true,
        ]);

        $this->post("/banners/{$banner->id}/impression")->assertNoContent();
        $this->assertSame(1, $banner->fresh()->impressions);
    }

    public function test_live_scope_only_returns_payable_active_in_range(): void
    {
        Banner::create(['title' => 'live', 'image_path' => 'x', 'placement' => 'home_top', 'status' => 'active', 'is_paid' => true]);
        Banner::create(['title' => 'unpaid', 'image_path' => 'x', 'placement' => 'home_top', 'status' => 'active', 'is_paid' => false]);
        Banner::create(['title' => 'pending', 'image_path' => 'x', 'placement' => 'home_top', 'status' => 'pending', 'is_paid' => true]);
        Banner::create(['title' => 'expired', 'image_path' => 'x', 'placement' => 'home_top', 'status' => 'active', 'is_paid' => true, 'ends_at' => now()->subDay()]);

        $live = Banner::live('home_top')->get();
        $this->assertCount(1, $live);
        $this->assertSame('live', $live->first()->title);
    }
}
