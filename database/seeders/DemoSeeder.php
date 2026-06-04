<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\NewsCategory;
use App\Models\Post;
use App\Models\Professional;
use App\Models\ProfessionalCategory;
use App\Models\Report;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->subCategories();
        $this->tags();
        $this->reports();
        $this->professionals();
        $this->guides();
    }

    private function guides(): void
    {
        $user = User::first();
        if (! $user) {
            return;
        }

        $demo = [
            ['title' => 'Германд хотын бүртгэл (Anmeldung) хийх', 'topic' => 'registration', 'country' => 'Герман', 'featured' => true, 'excerpt' => 'Шинээр нүүж ирээд 14 хоногийн дотор Bürgeramt дээр бүртгүүлэх алхмууд.'],
            ['title' => 'ВНЖ (Aufenthaltstitel) сунгах заавар', 'topic' => 'visa', 'country' => 'Герман', 'featured' => true, 'excerpt' => 'Цаг авах, бүрдүүлэх бичиг баримт, хураамж, анхаарах зүйлс.'],
            ['title' => 'Эрүүл мэндийн даатгал хэрхэн сонгох', 'topic' => 'insurance', 'country' => 'Герман', 'featured' => false, 'excerpt' => 'Gesetzlich vs privat — оюутан, ажилтанд аль нь тохиромжтой вэ.'],
            ['title' => 'Чехэд оюутны визээр ирэхэд', 'topic' => 'study', 'country' => 'Чех', 'featured' => false, 'excerpt' => 'Элсэлт, виз, ирсний дараах бүртгэлийн талаар.'],
            ['title' => 'Жолооны үнэмлэхээ хөрвүүлэх', 'topic' => 'driving', 'country' => 'Герман', 'featured' => false, 'excerpt' => 'Монгол үнэмлэхээ ЕХ-ны үнэмлэх рүү хөрвүүлэх боломж, нөхцөл.'],
            ['title' => 'Банкны данс нээх (Германд)', 'topic' => 'bank', 'country' => 'Герман', 'featured' => false, 'excerpt' => 'Шаардлагатай бичиг баримт, онлайн банкны сонголтууд.'],
        ];

        foreach ($demo as $d) {
            \App\Models\Guide::firstOrCreate(
                ['slug' => Str::slug($d['title'])],
                [
                    'user_id' => $user->id,
                    'title' => $d['title'],
                    'excerpt' => $d['excerpt'],
                    'body' => '<h2>Ерөнхий мэдээлэл</h2><p>'.$d['excerpt'].' Энэ нь жишээ агуулга (demo).</p><h3>Алхмууд</h3><ol><li>Цаг товлох / бүртгүүлэх</li><li>Бичиг баримт бүрдүүлэх</li><li>Холбогдох газартаа очих</li></ol><p>Дэлгэрэнгүйг албан ёсны эх сурвалжаас шалгана уу.</p>',
                    'topic' => $d['topic'],
                    'country' => $d['country'],
                    'is_featured' => $d['featured'],
                    'status' => 'published',
                    'published_at' => now(),
                ],
            );
        }
    }

    private function professionals(): void
    {
        $user = User::first();
        if (! $user || ProfessionalCategory::count() === 0) {
            return;
        }

        $byName = fn ($n) => ProfessionalCategory::where('name', $n)->first()?->id;

        $demo = [
            ['name' => 'Б. Болормаа', 'profession' => 'Гэр бүлийн хуульч', 'cat' => 'Хуульч', 'city' => 'Берлин', 'country' => 'Герман', 'langs' => ['Монгол', 'Герман'], 'verified' => true, 'featured' => true],
            ['name' => 'Д. Энхтайван', 'profession' => 'Шүдний эмч', 'cat' => 'Эмч / Эрүүл мэнд', 'city' => 'Мюнхен', 'country' => 'Герман', 'langs' => ['Монгол', 'Герман', 'Англи'], 'verified' => true, 'featured' => false],
            ['name' => 'С. Цэцэгмаа', 'profession' => 'Орчуулагч (DE↔MN)', 'cat' => 'Орчуулагч', 'city' => 'Франкфурт', 'country' => 'Герман', 'langs' => ['Монгол', 'Герман'], 'verified' => true, 'featured' => true],
            ['name' => 'Г. Тэмүүлэн', 'profession' => 'Үсчин', 'cat' => 'Үсчин / Гоо сайхан', 'city' => 'Прага', 'country' => 'Чех', 'langs' => ['Монгол', 'Чех'], 'verified' => false, 'featured' => false],
            ['name' => 'Н. Алтанцэцэг', 'profession' => 'Нягтлан бодогч', 'cat' => 'Бухгалтер / Татвар', 'city' => 'Вена', 'country' => 'Австри', 'langs' => ['Монгол', 'Англи'], 'verified' => true, 'featured' => true],
            ['name' => 'О. Батбаяр', 'profession' => 'Гэрэл зурагчин', 'cat' => 'Гэрэл зурагчин', 'city' => 'Берлин', 'country' => 'Герман', 'langs' => ['Монгол'], 'verified' => false, 'featured' => false],
        ];

        foreach ($demo as $d) {
            Professional::firstOrCreate(
                ['slug' => Str::slug($d['name'].'-'.$d['profession'])],
                [
                    'user_id' => $user->id,
                    'professional_category_id' => $byName($d['cat']),
                    'name' => $d['name'],
                    'profession' => $d['profession'],
                    'bio' => '<p>'.$d['profession'].'. Европ дахь монголчуудад мэргэжлийн үйлчилгээ үзүүлж байна. (demo)</p>',
                    'city' => $d['city'],
                    'country' => $d['country'],
                    'languages' => $d['langs'],
                    'services' => "Зөвлөгөө\nҮйлчилгээ\nДагалдан туслах",
                    'phone' => '+49 170 000 0000',
                    'email' => 'demo@eu-mongolia.test',
                    'status' => 'active',
                    'is_verified' => $d['verified'],
                    'is_featured' => $d['featured'],
                    'featured_until' => $d['featured'] ? now()->addDays(30) : null,
                ],
            );
        }
    }

    private function subCategories(): void
    {
        $parents = NewsCategory::whereNull('parent_id')->orderBy('sort_order')->take(3)->get();

        foreach ($parents as $parent) {
            foreach (['Дотоод', 'Гадаад'] as $sub) {
                NewsCategory::firstOrCreate(
                    ['slug' => Str::slug($parent->name.'-'.$sub)],
                    ['parent_id' => $parent->id, 'name' => $sub, 'sort_order' => 0],
                );
            }
        }
    }

    private function tags(): void
    {
        $names = ['Виз', 'Ажил', 'Боловсрол', 'Спорт', 'Соёл', 'Технологи', 'Эрүүл мэнд', 'Аялал', 'Бизнес', 'Хууль'];
        $tags = collect($names)->map(fn ($n) => Tag::findOrCreateByName($n));

        Post::all()->each(function (Post $post) use ($tags) {
            $post->tags()->syncWithoutDetaching(
                $tags->random(min(3, $tags->count()))->pluck('id')->all()
            );
        });
    }

    private function reports(): void
    {
        $user = User::first();
        if (! $user) {
            return;
        }

        $reasons = ['spam', 'scam', 'duplicate', 'prohibited', 'offensive'];

        Listing::where('status', 'active')->inRandomOrder()->take(3)->get()->each(function (Listing $listing) use ($user, $reasons) {
            Report::firstOrCreate(
                ['listing_id' => $listing->id, 'reporter_id' => $user->id, 'status' => 'pending'],
                ['reason' => $reasons[array_rand($reasons)], 'note' => 'Жишээ гомдол (demo).'],
            );
        });
    }
}
