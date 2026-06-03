<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    private function listing(): Listing
    {
        $cat = ListingCategory::firstOrCreate(['slug' => 'test'], ['name' => 'Тест']);

        return Listing::create([
            'user_id' => User::factory()->create()->id,
            'listing_category_id' => $cat->id,
            'title' => 'Зар', 'slug' => 'zar-fav', 'description' => 'd',
            'price' => 10, 'price_type' => 'fixed', 'status' => 'active',
        ]);
    }

    public function test_guest_cannot_favorite(): void
    {
        $listing = $this->listing();
        $this->post("/zar/{$listing->id}/favorite")->assertRedirect('/login');
    }

    public function test_user_can_toggle_favorite(): void
    {
        $listing = $this->listing();
        $user = User::factory()->create();

        // Хадгалах
        $this->actingAs($user)->post("/zar/{$listing->id}/favorite");
        $this->assertDatabaseHas('listing_user', ['user_id' => $user->id, 'listing_id' => $listing->id]);

        // Дахин дарвал хасагдана
        $this->actingAs($user)->post("/zar/{$listing->id}/favorite");
        $this->assertDatabaseMissing('listing_user', ['user_id' => $user->id, 'listing_id' => $listing->id]);
    }

    public function test_favorites_page_lists_saved(): void
    {
        $listing = $this->listing();
        $user = User::factory()->create();
        $user->favorites()->attach($listing->id);

        $this->actingAs($user)
            ->get('/my/favorites')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Zar/Favorites')->has('listings', 1));
    }
}
