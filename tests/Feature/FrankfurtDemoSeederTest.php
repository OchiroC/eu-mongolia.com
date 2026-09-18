<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Flight;
use App\Models\Guide;
use App\Models\Parcel;
use App\Models\User;
use Database\Seeders\FrankfurtDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FrankfurtDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_fills_new_features_and_is_idempotent(): void
    {
        Role::firstOrCreate(['name' => 'user']);
        (new FrankfurtDemoSeeder)->setContainer(app())->run();
        $counts = [Business::count(), Parcel::count(), User::count(), Flight::count()];

        (new FrankfurtDemoSeeder)->setContainer(app())->run();

        $this->assertSame($counts, [Business::count(), Parcel::count(), User::count(), Flight::count()]);
        $this->assertGreaterThan(0, Flight::count());
        $this->assertSame(5, Parcel::count());
        $this->assertSame(5, Business::whereNotNull('lat')->count());
    }

    public function test_demo_text_is_cleaned(): void
    {
        Guide::create([
            'title' => 'Туршилт — гарчиг', 'slug' => 'turshilt', 'body' => '<p>Агуулга (demo)</p>',
            'excerpt' => 'Gesetzlich vs privat', 'topic' => 'visa', 'status' => 'published', 'published_at' => now(),
        ]);

        (new FrankfurtDemoSeeder)->setContainer(app())->run();

        $g = Guide::where('slug', 'turshilt')->first();
        $this->assertSame('Туршилт, гарчиг', $g->title);
        $this->assertSame('<p>Агуулга</p>', $g->body);
        $this->assertSame('Gesetzlich эсвэл privat', $g->excerpt);
    }
}
