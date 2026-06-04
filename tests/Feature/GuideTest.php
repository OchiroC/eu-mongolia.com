<?php

namespace Tests\Feature;

use App\Models\Guide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GuideTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);
        $u = User::factory()->create();
        $u->assignRole('admin');

        return $u;
    }

    private function guide(array $attrs = []): Guide
    {
        return Guide::create(array_merge([
            'title' => 'Заавар', 'slug' => 'z-'.uniqid(), 'body' => '<p>b</p>',
            'topic' => 'visa', 'country' => 'Герман',
            'status' => 'published', 'published_at' => now(),
        ], $attrs));
    }

    public function test_public_sees_published_only(): void
    {
        $this->guide(['title' => 'PUBLISHED-GUIDE']);
        $this->guide(['title' => 'DRAFT-GUIDE', 'slug' => 'draft-g', 'status' => 'draft', 'published_at' => null]);

        $res = $this->get('/guides');
        $res->assertOk();
        $res->assertSee('PUBLISHED-GUIDE');
        $res->assertDontSee('DRAFT-GUIDE');
    }

    public function test_draft_detail_is_404(): void
    {
        $g = $this->guide(['status' => 'draft', 'published_at' => null]);
        $this->get("/guides/{$g->slug}")->assertNotFound();
    }

    public function test_show_increments_views(): void
    {
        $g = $this->guide(['views' => 0]);
        $this->get("/guides/{$g->slug}")->assertOk();
        $this->assertSame(1, $g->fresh()->views);
    }

    public function test_topic_filter_works(): void
    {
        $this->guide(['title' => 'VISA-ONE', 'topic' => 'visa']);
        $this->guide(['title' => 'TAX-ONE', 'topic' => 'tax', 'slug' => 'tax-one']);

        $this->get('/guides?topic=visa')->assertOk()->assertSee('VISA-ONE')->assertDontSee('TAX-ONE');
    }

    public function test_admin_can_create_guide(): void
    {
        $this->actingAs($this->admin())->post('/admin/guides', [
            'title' => 'Шинэ заавар',
            'body' => '<p>агуулга</p>',
            'topic' => 'registration',
            'country' => 'Герман',
            'status' => 'published',
        ])->assertRedirect('/admin/guides');

        $this->assertDatabaseHas('guides', ['title' => 'Шинэ заавар', 'topic' => 'registration', 'status' => 'published']);
        $this->assertNotNull(Guide::first()->published_at);
    }

    public function test_invalid_topic_rejected(): void
    {
        $this->actingAs($this->admin())->post('/admin/guides', [
            'title' => 'X', 'body' => 'b', 'topic' => 'nonsense', 'status' => 'draft',
        ])->assertSessionHasErrors('topic');
    }

    public function test_non_admin_cannot_manage(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/guides')->assertForbidden();
    }

    public function test_admin_can_delete(): void
    {
        $g = $this->guide();
        $this->actingAs($this->admin())->delete("/admin/guides/{$g->id}")->assertRedirect('/admin/guides');
        $this->assertDatabaseMissing('guides', ['id' => $g->id]);
    }
}
