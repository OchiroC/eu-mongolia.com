<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContentImageCleanupTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    private function putImages(string ...$names): void
    {
        foreach ($names as $n) {
            Storage::disk('public')->put("content/{$n}", 'fake');
        }
    }

    private function body(string ...$names): string
    {
        $imgs = collect($names)->map(fn ($n) => '<img src="/storage/content/'.$n.'">')->implode('');

        return '<p>Текст</p>'.$imgs;
    }

    public function test_removed_content_image_is_deleted_on_update(): void
    {
        Storage::fake('public');
        $this->putImages('keep.jpg', 'remove.jpg');

        $post = Post::create([
            'title' => 'Мэдээ', 'slug' => 'm1', 'body' => $this->body('keep.jpg', 'remove.jpg'),
            'status' => 'published', 'published_at' => now(),
        ]);

        $this->actingAs($this->admin())->put("/admin/posts/{$post->id}", [
            'title' => 'Мэдээ', 'body' => $this->body('keep.jpg'), 'status' => 'published',
        ])->assertRedirect('/admin/posts');

        Storage::disk('public')->assertExists('content/keep.jpg');
        Storage::disk('public')->assertMissing('content/remove.jpg');
    }

    public function test_all_content_images_deleted_on_destroy(): void
    {
        Storage::fake('public');
        $this->putImages('a.jpg', 'b.jpg');

        $post = Post::create([
            'title' => 'Мэдээ', 'slug' => 'm2', 'body' => $this->body('a.jpg', 'b.jpg'),
            'status' => 'published', 'published_at' => now(),
        ]);

        $this->actingAs($this->admin())->delete("/admin/posts/{$post->id}")
            ->assertRedirect('/admin/posts');

        Storage::disk('public')->assertMissing('content/a.jpg');
        Storage::disk('public')->assertMissing('content/b.jpg');
    }

    public function test_kept_images_survive_update(): void
    {
        Storage::fake('public');
        $this->putImages('x.jpg');

        $post = Post::create([
            'title' => 'Мэдээ', 'slug' => 'm3', 'body' => $this->body('x.jpg'),
            'status' => 'published', 'published_at' => now(),
        ]);

        $this->actingAs($this->admin())->put("/admin/posts/{$post->id}", [
            'title' => 'Шинэ нэр', 'body' => $this->body('x.jpg'), 'status' => 'published',
        ])->assertRedirect('/admin/posts');

        Storage::disk('public')->assertExists('content/x.jpg');
    }
}
