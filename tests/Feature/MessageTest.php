<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\User;
use App\Notifications\NewMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    private function listing(?User $owner = null): Listing
    {
        $cat = ListingCategory::firstOrCreate(['slug' => 'electronics'], ['name' => 'Электроник']);

        return Listing::create([
            'user_id' => ($owner ?? User::factory()->create())->id,
            'listing_category_id' => $cat->id,
            'title' => 'iPhone', 'slug' => 'iphone-'.uniqid(), 'description' => 'd',
            'price' => 100, 'price_type' => 'fixed', 'city' => 'Berlin', 'status' => 'active',
        ]);
    }

    public function test_guest_cannot_message(): void
    {
        $listing = $this->listing();
        $this->post("/zar/{$listing->id}/message", ['body' => 'Сайн уу'])
            ->assertRedirect('/login');
    }

    public function test_buyer_starts_conversation_and_seller_notified(): void
    {
        Notification::fake();
        $seller = User::factory()->create();
        $listing = $this->listing($seller);
        $buyer = User::factory()->create();

        $this->actingAs($buyer)
            ->post("/zar/{$listing->id}/message", ['body' => 'Энэ бараа байгаа юу?'])
            ->assertRedirect();

        $this->assertDatabaseHas('conversations', [
            'listing_id' => $listing->id, 'buyer_id' => $buyer->id, 'seller_id' => $seller->id,
        ]);
        $this->assertDatabaseHas('messages', ['body' => 'Энэ бараа байгаа юу?', 'sender_id' => $buyer->id]);
        Notification::assertSentTo($seller, NewMessage::class);
    }

    public function test_owner_cannot_message_own_listing(): void
    {
        $owner = User::factory()->create();
        $listing = $this->listing($owner);

        $this->actingAs($owner)->post("/zar/{$listing->id}/message", ['body' => 'x']);

        $this->assertDatabaseCount('conversations', 0);
    }

    public function test_starting_twice_reuses_conversation(): void
    {
        $seller = User::factory()->create();
        $listing = $this->listing($seller);
        $buyer = User::factory()->create();

        $this->actingAs($buyer)->post("/zar/{$listing->id}/message", ['body' => 'Нэг']);
        $this->actingAs($buyer)->post("/zar/{$listing->id}/message", ['body' => 'Хоёр']);

        $this->assertDatabaseCount('conversations', 1);
        $this->assertDatabaseCount('messages', 2);
    }

    public function test_outsider_cannot_view_conversation(): void
    {
        $seller = User::factory()->create();
        $listing = $this->listing($seller);
        $buyer = User::factory()->create();
        $conv = Conversation::create(['listing_id' => $listing->id, 'buyer_id' => $buyer->id, 'seller_id' => $seller->id]);

        $this->actingAs(User::factory()->create())
            ->get("/messages/{$conv->id}")
            ->assertForbidden();
    }

    public function test_viewing_thread_marks_messages_read(): void
    {
        $seller = User::factory()->create();
        $listing = $this->listing($seller);
        $buyer = User::factory()->create();
        $conv = Conversation::create(['listing_id' => $listing->id, 'buyer_id' => $buyer->id, 'seller_id' => $seller->id]);
        $conv->messages()->create(['sender_id' => $buyer->id, 'body' => 'Сайн уу']);

        // Худалдагч нээхэд буyer-ийн мессеж уншсан болно.
        $this->actingAs($seller)->get("/messages/{$conv->id}")->assertOk();

        $this->assertNotNull($conv->messages()->first()->fresh()->read_at);
    }

    public function test_reply_adds_message_and_notifies_other(): void
    {
        Notification::fake();
        $seller = User::factory()->create();
        $listing = $this->listing($seller);
        $buyer = User::factory()->create();
        $conv = Conversation::create(['listing_id' => $listing->id, 'buyer_id' => $buyer->id, 'seller_id' => $seller->id]);

        $this->actingAs($seller)->post("/messages/{$conv->id}", ['body' => 'Тийм, байгаа'])->assertRedirect();

        $this->assertDatabaseHas('messages', ['conversation_id' => $conv->id, 'sender_id' => $seller->id, 'body' => 'Тийм, байгаа']);
        Notification::assertSentTo($buyer, NewMessage::class);
    }
}
