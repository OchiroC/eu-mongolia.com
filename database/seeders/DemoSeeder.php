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
        $this->jobs();
        $this->questions();
        $this->answers();
        $this->comments();
        $this->embassies();
        $this->rides();
    }

    /** Контентын зохиогч — хэрэглэгч байхгүй бол үүсгэнэ (хоосон DB дээр ч seed ажиллана). */
    private function author(): User
    {
        return User::first() ?? User::create([
            'name' => 'EU Mongolia',
            'email' => 'demo@eu-mongolia.test',
            'password' => bcrypt(Str::random(32)),
            'email_verified_at' => now(),
        ]);
    }

    private function answers(): void
    {
        $user = $this->author();

        \App\Models\Question::where('answers_count', 0)->get()->each(function ($q) use ($user) {
            $a = $q->answers()->create([
                'user_id' => $user->id,
                'body' => 'Миний туршлагаас хэлэхэд эхлээд холбогдох байгууллагаас цаг авч, шаардлагатай бичиг баримтаа бүрдүүлээрэй. (demo хариулт)',
                'votes_count' => 0,
            ]);
            $q->update(['answers_count' => 1, 'best_answer_id' => $a->id]);
        });
    }

    private function comments(): void
    {
        $user = $this->author();

        Post::query()->latest('published_at')->take(3)->get()->each(function (Post $post) use ($user) {
            if ($post->comments()->exists()) {
                return;
            }
            $post->comments()->create([
                'user_id' => $user->id,
                'body' => 'Сонирхолтой мэдээ байна, баярлалаа! (demo)',
                'status' => 'approved',
            ]);
        });
    }

    private function rides(): void
    {
        $user = $this->author();

        $demo = [
            ['from' => 'Берлин', 'fc' => 'Герман', 'to' => 'Мюнхен', 'tc' => 'Герман', 'days' => 3, 'seats' => 3, 'price' => '€25'],
            ['from' => 'Прага', 'fc' => 'Чех', 'to' => 'Берлин', 'tc' => 'Герман', 'days' => 5, 'seats' => 2, 'price' => '€30'],
            ['from' => 'Вена', 'fc' => 'Австри', 'to' => 'Прага', 'tc' => 'Чех', 'days' => 7, 'seats' => 4, 'price' => 'Тохиролцоно'],
        ];

        foreach ($demo as $d) {
            \App\Models\Ride::firstOrCreate(
                ['user_id' => $user->id, 'from_city' => $d['from'], 'to_city' => $d['to'], 'depart_at' => now()->addDays($d['days'])->setTime(9, 0)],
                [
                    'from_country' => $d['fc'], 'to_country' => $d['tc'],
                    'seats' => $d['seats'], 'price' => $d['price'],
                    'notes' => 'Цуглах цэгийг тохиролцоно. (demo)',
                    'contact_phone' => '+49 170 0000000',
                    'status' => 'active',
                ],
            );
        }
    }

    private function embassies(): void
    {
        $demo = [
            ['name' => 'Монгол Улсаас ХБНГУ-д суугаа ЭСЯ', 'country' => 'Герман', 'city' => 'Берлин', 'website' => 'https://berlin.embassy.mn', 'sort' => 1],
            ['name' => 'Монгол Улсаас Австри Улсад суугаа ЭСЯ', 'country' => 'Австри', 'city' => 'Вена', 'website' => 'https://vienna.embassy.mn', 'sort' => 2],
            ['name' => 'Монгол Улсаас Чех Улсад суугаа ЭСЯ', 'country' => 'Чех', 'city' => 'Прага', 'website' => 'https://prague.embassy.mn', 'sort' => 3],
        ];

        foreach ($demo as $d) {
            \App\Models\Embassy::firstOrCreate(
                ['name' => $d['name']],
                [
                    'kind' => 'embassy',
                    'country' => $d['country'],
                    'city' => $d['city'],
                    'website' => $d['website'],
                    'notes' => 'Холбоо барих мэдээллийг албан ёсны вэбсайтаас баталгаажуулна уу. (demo)',
                    'sort_order' => $d['sort'],
                    'is_active' => true,
                ],
            );
        }
    }

    private function questions(): void
    {
        $user = $this->author();

        $demo = [
            ['title' => 'Германд оюутны визээ хэрхэн сунгах вэ?', 'cat' => 'visa', 'country' => 'Герман'],
            ['title' => 'Берлинд хямд орон сууц яаж олох вэ?', 'cat' => 'housing', 'country' => 'Герман'],
            ['title' => 'Чехэд цагийн ажил олоход юу анхаарах вэ?', 'cat' => 'work', 'country' => 'Чех'],
            ['title' => 'Гэр бүлийн эмч (Hausarzt) яаж бүртгүүлэх вэ?', 'cat' => 'health', 'country' => 'Герман'],
        ];

        foreach ($demo as $d) {
            \App\Models\Question::firstOrCreate(
                ['slug' => Str::slug($d['title'])],
                [
                    'user_id' => $user->id,
                    'title' => $d['title'],
                    'body' => $d['title'].' Хэн нэг туршлагатай хүн зөвлөгөө өгөөч. (demo)',
                    'category' => $d['cat'],
                    'country' => $d['country'],
                ],
            );
        }
    }

    private function jobs(): void
    {
        $user = $this->author();

        $demo = [
            ['title' => 'Ресторанд туслах ажилтан', 'company' => 'Khaan Buuz', 'type' => 'part_time', 'cat' => 'service', 'city' => 'Берлин', 'country' => 'Герман', 'salary' => '€13/цаг'],
            ['title' => 'Агуулахын ажилтан', 'company' => 'Logistik GmbH', 'type' => 'full_time', 'cat' => 'logistics', 'city' => 'Мюнхен', 'country' => 'Герман', 'salary' => '€2,400/сар'],
            ['title' => 'Цэвэрлэгээний ажилтан', 'company' => null, 'type' => 'part_time', 'cat' => 'cleaning', 'city' => 'Прага', 'country' => 'Чех', 'salary' => 'Тохиролцоно'],
            ['title' => 'Настан асрах туслах', 'company' => 'Pflege Plus', 'type' => 'full_time', 'cat' => 'care', 'city' => 'Вена', 'country' => 'Австри', 'salary' => '€2,100/сар'],
            ['title' => 'Веб хөгжүүлэгч (хагас цаг)', 'company' => 'Remote', 'type' => 'part_time', 'cat' => 'it', 'city' => null, 'country' => 'Герман', 'salary' => '€20/цаг'],
        ];

        foreach ($demo as $d) {
            \App\Models\JobPost::firstOrCreate(
                ['slug' => Str::slug($d['title'].'-'.($d['company'] ?? $d['city']))],
                [
                    'user_id' => $user->id,
                    'title' => $d['title'],
                    'company' => $d['company'],
                    'description' => $d['title'].' — дэлгэрэнгүй мэдээллийг холбоо барьж асууна уу. (demo)',
                    'employment_type' => $d['type'],
                    'category' => $d['cat'],
                    'city' => $d['city'],
                    'country' => $d['country'],
                    'salary' => $d['salary'],
                    'contact_email' => 'demo@eu-mongolia.test',
                    'status' => 'active',
                ],
            );
        }
    }

    private function guides(): void
    {
        $user = $this->author();

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
        $user = $this->author();
        if (ProfessionalCategory::count() === 0) {
            (new ProfessionalCategorySeeder)->run();
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
        $user = $this->author();

        $reasons = ['spam', 'scam', 'duplicate', 'prohibited', 'offensive'];

        Listing::where('status', 'active')->inRandomOrder()->take(3)->get()->each(function (Listing $listing) use ($user, $reasons) {
            Report::firstOrCreate(
                ['listing_id' => $listing->id, 'reporter_id' => $user->id, 'status' => 'pending'],
                ['reason' => $reasons[array_rand($reasons)], 'note' => 'Жишээ гомдол (demo).'],
            );
        });
    }
}
