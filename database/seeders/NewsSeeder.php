<?php

namespace Database\Seeders;

use App\Models\NewsCategory;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Визийн мэдээ', 'Ажил', 'Эрүүл мэнд', 'Соёл', 'Нийгэм',
        ];

        $catModels = [];
        foreach ($categories as $i => $name) {
            $catModels[] = NewsCategory::firstOrCreate(
                ['slug' => Str::slug($name) ?: 'angilal-'.$i],
                ['name' => $name, 'sort_order' => $i]
            );
        }

        $author = User::where('email', 'admin@eu-mongolia.test')->first();

        $samples = [
            ['Германд ажиллах визийн шинэ журам', 'Германы ажиллах хүчний виз 2026 онд хялбарчлагдлаа.', 'Герман'],
            ['Прага дахь Цагаан сарын баяр', 'Чех дэх монголчууд Цагаан сараа хамтдаа тэмдэглэнэ.', 'Чех'],
            ['Европ дахь монгол эмч нарын сүлжээ', 'Эрүүл мэндийн зөвлөгөө өгөх сайн дурын сүлжээ байгуулагдлаа.', 'Бусад'],
            ['Польшид оюутны тэтгэлэг нээгдлээ', 'Монгол оюутнуудад зориулсан тэтгэлгийн мэдээлэл.', 'Польш'],
        ];

        foreach ($samples as $i => [$title, $excerpt, $country]) {
            Post::firstOrCreate(
                ['slug' => Str::slug($title) ?: 'medee-'.$i],
                [
                    'news_category_id' => $catModels[$i % count($catModels)]->id,
                    'user_id' => $author?->id,
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'body' => $excerpt."\n\n".str_repeat('Энэ бол жишээ агуулга. ', 20),
                    'country' => $country,
                    'status' => 'published',
                    'published_at' => now()->subDays($i),
                ]
            );
        }
    }
}
