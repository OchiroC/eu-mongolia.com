<?php

namespace Tests\Feature;

use App\Models\NewsCategory;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CategoryDeletionTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_parent_with_children_cannot_be_deleted(): void
    {
        $parent = NewsCategory::create(['name' => 'Эцэг', 'slug' => 'etseg']);
        NewsCategory::create(['name' => 'Дэд', 'slug' => 'ded', 'parent_id' => $parent->id]);

        $this->actingAs($this->admin())
            ->delete("/admin/categories/news/{$parent->id}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('news_categories', ['id' => $parent->id]);
    }

    public function test_subcategory_deletion_moves_posts_to_uncategorized(): void
    {
        $parent = NewsCategory::create(['name' => 'Эцэг', 'slug' => 'etseg']);
        $sub = NewsCategory::create(['name' => 'Дэд', 'slug' => 'ded', 'parent_id' => $parent->id]);
        $post = Post::create([
            'title' => 'Мэдээ', 'slug' => 'm-'.uniqid(), 'body' => 'b',
            'status' => 'published', 'published_at' => now(), 'news_category_id' => $sub->id,
        ]);

        $this->actingAs($this->admin())
            ->delete("/admin/categories/news/{$sub->id}")
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('news_categories', ['id' => $sub->id]);

        $uncategorized = NewsCategory::where('slug', 'uncategorized')->first();
        $this->assertNotNull($uncategorized, 'Ангилалгүй автоматаар үүсэх ёстой');
        $this->assertSame($uncategorized->id, $post->fresh()->news_category_id);
    }

    public function test_uncategorized_category_cannot_be_deleted(): void
    {
        $uncat = NewsCategory::uncategorized();

        $this->actingAs($this->admin())
            ->delete("/admin/categories/news/{$uncat->id}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('news_categories', ['id' => $uncat->id]);
    }

    public function test_empty_category_is_deleted_normally(): void
    {
        $cat = NewsCategory::create(['name' => 'Хоосон', 'slug' => 'empty']);

        $this->actingAs($this->admin())
            ->delete("/admin/categories/news/{$cat->id}")
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('news_categories', ['id' => $cat->id]);
    }

    public function test_renaming_uncategorized_keeps_its_slug(): void
    {
        $uncat = NewsCategory::uncategorized();

        $this->actingAs($this->admin())
            ->put("/admin/categories/news/{$uncat->id}", ['name' => 'Өөр нэр']);

        $this->assertSame('uncategorized', $uncat->fresh()->slug);
    }
}
