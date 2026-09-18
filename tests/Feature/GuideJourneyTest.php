<?php

namespace Tests\Feature;

use App\Models\Guide;
use App\Models\User;
use Database\Seeders\JourneyGuidesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GuideJourneyTest extends TestCase
{
    use RefreshDatabase;

    private function guide(array $attrs = []): Guide
    {
        return Guide::create(array_merge([
            'title' => 'Заавар', 'slug' => 'z-'.uniqid(), 'body' => '<p>b</p>',
            'topic' => 'visa', 'country' => 'Герман',
            'status' => 'published', 'published_at' => now(),
        ], $attrs));
    }

    public function test_home_groups_staged_guides_in_stage_order(): void
    {
        $this->guide(['title' => 'SECOND', 'stage' => 'before', 'stage_order' => 2]);
        $this->guide(['title' => 'FIRST', 'stage' => 'before', 'stage_order' => 1]);
        $this->guide(['title' => 'LANDING', 'stage' => 'arrival', 'stage_order' => 1]);

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('journey', 3)
            ->where('journey.0.key', 'before')
            ->where('journey.0.guides.0.title', 'FIRST')
            ->where('journey.0.guides.1.title', 'SECOND')
            ->where('journey.1.key', 'arrival')
            ->where('journey.1.guides.0.title', 'LANDING')
            ->has('journey.2.guides', 0)
        );
    }

    public function test_drafts_and_unstaged_guides_are_not_in_journey(): void
    {
        $this->guide(['title' => 'DRAFT', 'stage' => 'before', 'status' => 'draft', 'published_at' => null]);
        $this->guide(['title' => 'NO-STAGE']);

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->has('journey.0.guides', 0)
            ->has('journey.1.guides', 0)
            ->has('journey.2.guides', 0)
        );
    }

    public function test_admin_can_set_stage(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $g = $this->guide();

        $this->actingAs($admin)->put("/admin/guides/{$g->id}", [
            'title' => $g->title, 'body' => $g->body, 'topic' => 'travel',
            'stage' => 'arrival', 'stage_order' => 3, 'status' => 'published',
        ])->assertRedirect();

        $this->assertSame('arrival', $g->fresh()->stage);
        $this->assertSame(3, $g->fresh()->stage_order);
    }

    public function test_invalid_stage_is_rejected(): void
    {
        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $g = $this->guide();

        $this->actingAs($admin)->put("/admin/guides/{$g->id}", [
            'title' => $g->title, 'body' => $g->body, 'topic' => 'visa',
            'stage' => 'moon', 'status' => 'published',
        ])->assertSessionHasErrors('stage');
    }

    public function test_seeder_is_idempotent(): void
    {
        User::factory()->create();
        (new JourneyGuidesSeeder)->run();
        (new JourneyGuidesSeeder)->run();

        $this->assertSame(6, Guide::whereNotNull('stage')->count());
        $this->assertSame(7, Guide::count());
        $this->assertSame(1, Guide::where('slug', 'frankfurtyn-nisex-buudlaas-xot-ruu')->count());
    }
}
