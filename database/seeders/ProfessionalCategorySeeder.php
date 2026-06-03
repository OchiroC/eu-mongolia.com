<?php

namespace Database\Seeders;

use App\Models\ProfessionalCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProfessionalCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Хуульч', 'icon' => 'briefcase'],
            ['name' => 'Эмч', 'icon' => 'wrench'],
            ['name' => 'Орчуулагч', 'icon' => 'tag'],
            ['name' => 'Үсчин / Гоо сайхан', 'icon' => 'shirt'],
            ['name' => 'Засвар үйлчилгээ', 'icon' => 'wrench'],
            ['name' => 'Багш / Хичээл', 'icon' => 'briefcase'],
            ['name' => 'Бухгалтер / Татвар', 'icon' => 'briefcase'],
            ['name' => 'Гэрэл зурагчин', 'icon' => 'device'],
            ['name' => 'Бусад', 'icon' => 'tag'],
        ];

        foreach ($categories as $i => $cat) {
            ProfessionalCategory::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                ['name' => $cat['name'], 'icon' => $cat['icon'], 'sort_order' => $i],
            );
        }
    }
}
