<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingPromoteTest extends TestCase
{
    use RefreshDatabase;

    private function listing(?User $owner = null, array $attrs = []): Listing
    {
        $cat = ListingCategory::firstOrCreate(['slug' => 'electronics'], ['name' => 'Электроник']);

        return Listing::create(array_merge([
            'user_id' => ($owner ?? User::factory()->create())->id,
            'listing_category_id' => $cat->id,
            'title' => 'iPhone', 'slug' => 'iphone-'.uniqid(), 'description' => 'd',
            'price' => 100, 'price_type' => 'fixed', 'city' => 'Berlin', 'status' => 'active',
        ], $attrs));
    }

    public function test_owner_can_promote_listing(): void
    {
        $owner = User::factory()->create();
        $listing = $this->listing($owner);

        $this->actingAs($owner)->post("/zar/{$listing->id}/promote", ['days' => 30])->assertRedirect();

        $listing->refresh();
        $this->assertTrue($listing->is_featured);
        $this->assertTrue($listing->isCurrentlyFeatured());
        $this->assertTrue($listing->featured_until->isFuture());
    }

    public function test_non_owner_cannot_promote(): void
    {
        $listing = $this->listing();
        $this->actingAs(User::factory()->create())
            ->post("/zar/{$listing->id}/promote", ['days' => 7])
            ->assertForbidden();

        $this->assertFalse($listing->fresh()->is_featured);
    }

    public function test_invalid_package_rejected(): void
    {
        $owner = User::factory()->create();
        $listing = $this->listing($owner);

        $this->actingAs($owner)->post("/zar/{$listing->id}/promote", ['days' => 99])
            ->assertSessionHasErrors('days');
    }

    public function test_promote_extends_existing_feature(): void
    {
        $owner = User::factory()->create();
        $until = now()->addDays(5);
        $listing = $this->listing($owner, ['is_featured' => true, 'featured_until' => $until]);

        $this->actingAs($owner)->post("/zar/{$listing->id}/promote", ['days' => 7]);

        // 5 хоног дээр 7 нэмж ~12 хоног болно (одооноос биш).
        $this->assertTrue($listing->fresh()->featured_until->gt(now()->addDays(10)));
    }

    public function test_expired_feature_not_shown_as_featured(): void
    {
        $listing = $this->listing(null, ['is_featured' => true, 'featured_until' => now()->subDay()]);

        $this->assertFalse($listing->isCurrentlyFeatured());

        // Жагсаалтад онцлох badge-гүй (is_featured=false дамжина).
        $res = $this->getJson('/zar')->assertOk();
        // currentlyFeatured scope-д орохгүй
        $this->assertSame(0, Listing::active()->currentlyFeatured()->count());
    }

    public function test_currently_featured_sorts_first(): void
    {
        $cat = ListingCategory::firstOrCreate(['slug' => 'electronics'], ['name' => 'Электроник']);
        // Хуучин onцлох (хугацаа дуусаагүй) ба шинэ энгийн
        $featured = $this->listing(null, ['title' => 'FEATURED', 'is_featured' => true, 'featured_until' => now()->addDays(10), 'created_at' => now()->subDays(5)]);
        $plain = $this->listing(null, ['title' => 'PLAIN', 'created_at' => now()]);

        $first = Listing::active()->orderedForList()->first();
        $this->assertSame($featured->id, $first->id);
    }
}
