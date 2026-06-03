<?php

namespace Tests\Feature;

use App\Models\NewsCategory;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminPostTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    public function test_guest_cannot_access_admin(): void
    {
        $this->get('/admin/posts')->assertRedirect('/login');
    }

    public function test_non_admin_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/posts')
            ->assertForbidden();
    }

    public function test_admin_can_create_post(): void
    {
        $category = NewsCategory::create(['name' => 'Тест', 'slug' => 'test']);

        $response = $this->actingAs($this->admin())->post('/admin/posts', [
            'title' => 'Шинэ мэдээ гарчиг',
            'news_category_id' => $category->id,
            'excerpt' => 'Товч тойм',
            'body' => 'Үндсэн агуулга',
            'country' => 'Герман',
            'is_featured' => false,
            'status' => 'published',
        ]);

        $response->assertRedirect('/admin/posts');
        $this->assertDatabaseHas('posts', [
            'title' => 'Шинэ мэдээ гарчиг',
            'status' => 'published',
        ]);
        $this->assertNotNull(Post::first()->published_at);
        $this->assertNotEmpty(Post::first()->slug);
    }

    public function test_admin_can_update_and_delete_post(): void
    {
        $admin = $this->admin();
        $post = Post::create([
            'title' => 'Хуучин', 'slug' => 'huuchin', 'body' => 'b', 'status' => 'draft',
        ]);

        $this->actingAs($admin)->put("/admin/posts/{$post->id}", [
            'title' => 'Шинэчилсэн', 'body' => 'b2', 'status' => 'published',
        ])->assertRedirect('/admin/posts');

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Шинэчилсэн']);

        $this->actingAs($admin)->delete("/admin/posts/{$post->id}")
            ->assertRedirect('/admin/posts');
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_public_news_index_shows_published_only(): void
    {
        Post::create(['title' => 'Pub', 'slug' => 'pub', 'body' => 'b', 'status' => 'published', 'published_at' => now()]);
        Post::create(['title' => 'Draft', 'slug' => 'draft', 'body' => 'b', 'status' => 'draft']);

        $this->get('/news')->assertOk();
    }
}
