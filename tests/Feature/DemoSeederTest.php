<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Embassy;
use App\Models\Guide;
use App\Models\JobPost;
use App\Models\Professional;
use App\Models\Question;
use App\Models\Ride;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_populates_even_on_empty_db(): void
    {
        // Хоосон DB (хэрэглэгчгүй) — author() өөрөө хэрэглэгч үүсгэх ёстой.
        $this->assertSame(0, User::count());

        $this->seed(DemoSeeder::class);

        $this->assertGreaterThan(0, User::count(), 'author үүсэх ёстой');
        $this->assertGreaterThan(0, Professional::count());
        $this->assertGreaterThan(0, Guide::count());
        $this->assertGreaterThan(0, JobPost::count());
        $this->assertGreaterThan(0, Question::count());
        $this->assertGreaterThan(0, Answer::count());
        $this->assertGreaterThan(0, Embassy::count());
        $this->assertGreaterThan(0, Ride::count());
    }

    public function test_demo_seeder_is_idempotent(): void
    {
        $this->seed(DemoSeeder::class);
        $first = Ride::count();
        $this->seed(DemoSeeder::class);

        $this->assertSame($first, Ride::count(), 'дахин ажиллахад давхардахгүй');
    }
}
