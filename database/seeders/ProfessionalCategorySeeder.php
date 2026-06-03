<?php

namespace Database\Seeders;

use App\Models\ProfessionalCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProfessionalCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Европ дахь монголчуудад бодитоор хэрэгтэй ангиллууд.
        $categories = [
            ['name' => 'Хуульч', 'icon' => 'briefcase'],
            ['name' => 'Виз / Цагаачлал', 'icon' => 'doc'],
            ['name' => 'Эмч / Эрүүл мэнд', 'icon' => 'heart'],
            ['name' => 'Орчуулагч', 'icon' => 'globe'],
            ['name' => 'Аялал жуулчлал / Хөтөч', 'icon' => 'map'],
            ['name' => 'Үл хөдлөх / Орон сууц', 'icon' => 'home'],
            ['name' => 'Тээвэр / Нүүлгэлт', 'icon' => 'truck'],
            ['name' => 'Авто засвар', 'icon' => 'car'],
            ['name' => 'Үсчин / Гоо сайхан', 'icon' => 'shirt'],
            ['name' => 'Боловсрол / Хичээл', 'icon' => 'book'],
            ['name' => 'Бухгалтер / Татвар', 'icon' => 'briefcase'],
            ['name' => 'IT / Веб', 'icon' => 'device'],
            ['name' => 'Гэрэл зурагчин', 'icon' => 'camera'],
            ['name' => 'Гэрээ / Бичиг баримт', 'icon' => 'doc'],
            ['name' => 'Бусад', 'icon' => 'tag'],
        ];

        foreach ($categories as $i => $cat) {
            ProfessionalCategory::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                ['name' => $cat['name'], 'icon' => $cat['icon'], 'sort_order' => $i],
            );
        }
    }
}
