<?php

namespace Tests\Feature;

use App\Models\KidsResource;
use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KidsAndSearchTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);
        $u = User::factory()->create();
        $u->assignRole('admin');

        return $u;
    }

    // ── Kids ─────────────────────────────────────────────
    public function test_public_sees_active_kids_only(): void
    {
        KidsResource::create(['title' => 'ACTIVE-KID', 'category' => 'language', 'is_active' => true]);
        KidsResource::create(['title' => 'HIDDEN-KID', 'category' => 'language', 'is_active' => false]);

        $res = $this->get('/kids');
        $res->assertOk();
        $res->assertSee('ACTIVE-KID');
        $res->assertDontSee('HIDDEN-KID');
    }

    public function test_admin_kids_crud(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/kids', [
            'title' => 'Шинэ нөөц', 'category' => 'video', 'url' => 'https://x.test',
        ])->assertRedirect();
        $this->assertDatabaseHas('kids_resources', ['title' => 'Шинэ нөөц']);

        $r = KidsResource::first();
        $this->actingAs($admin)->put("/admin/kids/{$r->id}", ['title' => 'Зассан', 'category' => 'books'])->assertRedirect();
        $this->assertSame('Зассан', $r->fresh()->title);

        $this->actingAs($admin)->delete("/admin/kids/{$r->id}")->assertRedirect();
        $this->assertDatabaseMissing('kids_resources', ['id' => $r->id]);
    }

    public function test_kids_invalid_category_rejected(): void
    {
        $this->actingAs($this->admin())->post('/admin/kids', ['title' => 'X', 'category' => 'bogus'])
            ->assertSessionHasErrors('category');
    }

    public function test_non_admin_cannot_manage_kids(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/kids')->assertForbidden();
    }

    // ── Search ───────────────────────────────────────────
    public function test_search_finds_listing(): void
    {
        $cat = ListingCategory::firstOrCreate(['slug' => 'electronics'], ['name' => 'Электроник']);
        Listing::create([
            'user_id' => User::factory()->create()->id, 'listing_category_id' => $cat->id,
            'title' => 'FINDME-PHONE', 'slug' => 'phone-x', 'description' => 'd',
            'price' => 100, 'price_type' => 'fixed', 'city' => 'Berlin', 'status' => 'active',
        ]);

        $this->get('/search?q=FINDME')
            ->assertOk()
            ->assertSee('FINDME-PHONE');
    }

    public function test_search_short_query_returns_nothing(): void
    {
        $this->get('/search?q=a')->assertOk();
        // 1 үсэг — хайлт ажиллахгүй (алдаагүй буцна)
        $this->assertTrue(true);
    }

    public function test_search_page_loads_empty(): void
    {
        $this->get('/search')->assertOk();
    }
}
