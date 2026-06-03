<?php

namespace Tests\Feature;

use App\Models\ListingCategory;
use App\Models\NewsCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccountManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_blocked_user_is_logged_out(): void
    {
        $blocked = User::factory()->create(['blocked_at' => now()]);

        $this->actingAs($blocked)->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_admin_account_cannot_be_deleted(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->delete('/profile', ['password' => 'password']);

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_block_and_unblock_user(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create();

        $this->actingAs($admin)->post("/admin/users/{$target->id}/toggle-block")->assertRedirect();
        $this->assertNotNull($target->fresh()->blocked_at);

        $this->actingAs($admin)->post("/admin/users/{$target->id}/toggle-block")->assertRedirect();
        $this->assertNull($target->fresh()->blocked_at);
    }

    public function test_category_with_items_cannot_be_deleted(): void
    {
        $admin = $this->admin();
        $cat = ListingCategory::firstOrCreate(['slug' => 'electronics'], ['name' => 'Электроник']);
        \App\Models\Listing::create([
            'user_id' => User::factory()->create()->id,
            'listing_category_id' => $cat->id,
            'title' => 'X', 'slug' => 'x-'.uniqid(), 'description' => 'd',
            'price' => 1, 'price_type' => 'fixed', 'city' => 'Berlin', 'status' => 'active',
        ]);

        $this->actingAs($admin)->delete("/admin/categories/listing/{$cat->id}");

        $this->assertDatabaseHas('listing_categories', ['id' => $cat->id]);
    }

    public function test_admin_can_create_news_subcategory(): void
    {
        $admin = $this->admin();
        $parent = NewsCategory::create(['name' => 'Эх орон', 'slug' => 'home']);

        $this->actingAs($admin)->post('/admin/categories/news', [
            'name' => 'Дотоод',
            'parent_id' => $parent->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('news_categories', [
            'name' => 'Дотоод',
            'parent_id' => $parent->id,
        ]);
    }
}
