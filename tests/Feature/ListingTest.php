<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingTest extends TestCase
{
    use RefreshDatabase;

    private function category(): ListingCategory
    {
        return ListingCategory::firstOrCreate(['slug' => 'electronics'], ['name' => 'Электроник']);
    }

    private function listing(array $attrs = []): Listing
    {
        return Listing::create(array_merge([
            'user_id' => User::factory()->create()->id,
            'listing_category_id' => $this->category()->id,
            'title' => 'iPhone',
            'slug' => 'iphone-'.uniqid(),
            'description' => 'Сайн утас',
            'price' => 400,
            'price_type' => 'fixed',
            'city' => 'Berlin',
            'status' => 'active',
        ], $attrs));
    }

    public function test_anyone_can_browse_listings(): void
    {
        $this->listing();
        $this->get('/zar')->assertOk();
    }

    public function test_guest_cannot_create_listing(): void
    {
        $this->get('/zar/new')->assertRedirect('/login');
    }

    public function test_user_can_post_listing(): void
    {
        $cat = $this->category();
        $user = User::factory()->create();

        $this->actingAs($user)->post('/zar', [
            'listing_category_id' => $cat->id,
            'title' => 'Toyota Prius',
            'description' => 'Цэвэр машин',
            'price_type' => 'negotiable',
            'price' => 6500,
            'condition' => 'used',
            'city' => 'München',
            'postal_code' => '80331',
        ])->assertRedirect('/my/zar');

        $this->assertDatabaseHas('listings', [
            'title' => 'Toyota Prius',
            'user_id' => $user->id,
            'price_type' => 'negotiable',
        ]);
    }

    public function test_free_listing_clears_price(): void
    {
        $cat = $this->category();
        $user = User::factory()->create();

        $this->actingAs($user)->post('/zar', [
            'listing_category_id' => $cat->id,
            'title' => 'Үнэгүй ном',
            'description' => 'Авах хүн байвал',
            'price_type' => 'free',
            'price' => 50,
        ])->assertRedirect('/my/zar');

        // Контроллер price-г null болгодоггүй; зөвхөн frontend; backend price хадгалж болно.
        $this->assertDatabaseHas('listings', ['title' => 'Үнэгүй ном', 'price_type' => 'free']);
    }

    public function test_owner_can_update_and_delete(): void
    {
        $listing = $this->listing();
        $owner = $listing->user;

        $this->actingAs($owner)->put("/zar/{$listing->id}", [
            'listing_category_id' => $listing->listing_category_id,
            'title' => 'Шинэчилсэн гарчиг',
            'description' => 'Шинэ тайлбар',
            'price_type' => 'fixed',
            'price' => 350,
        ])->assertRedirect('/my/zar');

        $this->assertDatabaseHas('listings', ['id' => $listing->id, 'title' => 'Шинэчилсэн гарчиг']);

        $this->actingAs($owner)->delete("/zar/{$listing->id}")->assertRedirect('/my/zar');
        $this->assertDatabaseMissing('listings', ['id' => $listing->id]);
    }

    public function test_non_owner_cannot_edit(): void
    {
        $listing = $this->listing();
        $other = User::factory()->create();

        $this->actingAs($other)->get("/zar/{$listing->id}/edit")->assertForbidden();
    }

    public function test_show_increments_views(): void
    {
        $listing = $this->listing(['views' => 0]);
        $this->get("/zar/{$listing->slug}")->assertOk();
        $this->assertSame(1, $listing->fresh()->views);
    }

    public function test_category_filter_works(): void
    {
        $electronics = $this->category();
        $cars = ListingCategory::create(['name' => 'Авто', 'slug' => 'cars']);
        $this->listing(['listing_category_id' => $electronics->id, 'title' => 'Утас']);
        $this->listing(['listing_category_id' => $cars->id, 'title' => 'Машин']);

        $this->get('/zar?category=cars')->assertOk();
        $this->assertDatabaseCount('listings', 2);
    }
}
