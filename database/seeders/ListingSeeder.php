<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\ListingCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ListingSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Авто машин', 'icon' => 'car'],
            ['name' => 'Үл хөдлөх / Орон сууц', 'icon' => 'home'],
            ['name' => 'Ажлын байр', 'icon' => 'briefcase'],
            ['name' => 'Электроник', 'icon' => 'device'],
            ['name' => 'Гэр ахуй / Тавилга', 'icon' => 'sofa'],
            ['name' => 'Хувцас / Гоо сайхан', 'icon' => 'shirt'],
            ['name' => 'Үйлчилгээ', 'icon' => 'wrench'],
            ['name' => 'Бусад', 'icon' => 'tag'],
        ];

        $catModels = [];
        foreach ($categories as $i => $c) {
            $catModels[] = ListingCategory::firstOrCreate(
                ['slug' => Str::slug($c['name']) ?: 'ang-'.$i],
                ['name' => $c['name'], 'icon' => $c['icon'], 'sort_order' => $i]
            );
        }

        $user = User::where('email', 'admin@eu-mongolia.test')->first();
        if (! $user) {
            return;
        }

        $samples = [
            ['Авто машин', 'Toyota Prius 2015, маш цэвэр', 6500, 'fixed', 'used', 'Berlin', '10115'],
            ['Үл хөдлөх / Орон сууц', '2 өрөө байр түрээслүүлнэ (Prenzlauer Berg)', 950, 'fixed', null, 'Berlin', '10405'],
            ['Электроник', 'iPhone 13 128GB, баталгаатай', 480, 'negotiable', 'used', 'München', '80331'],
            ['Гэр ахуй / Тавилга', 'IKEA буйдан, цагаан өнгөтэй', 120, 'negotiable', 'used', 'Hamburg', '20095'],
            ['Хувцас / Гоо сайхан', 'Хүүхдийн хувцас багц (үнэгүй)', null, 'free', 'used', 'Köln', '50667'],
            ['Ажлын байр', 'Монгол ресторанд тогооч хайж байна', null, 'fixed', null, 'Frankfurt', '60311'],
            ['Үйлчилгээ', 'Монгол хэлний хичээл (онлайн/танхим)', 25, 'fixed', null, 'Wien', '1010'],
            ['Авто машин', 'Өвлийн дугуй 4ш, 205/55 R16', 200, 'negotiable', 'used', 'Praha', '11000'],
        ];

        foreach ($samples as $i => [$catName, $title, $price, $priceType, $cond, $city, $plz]) {
            $cat = collect($catModels)->firstWhere('name', $catName);
            Listing::firstOrCreate(
                ['slug' => Str::slug($title) ?: 'zar-'.$i],
                [
                    'user_id' => $user->id,
                    'listing_category_id' => $cat->id,
                    'title' => $title,
                    'description' => $title.".\n\nДэлгэрэнгүй мэдээллийг утсаар лавлана уу. Ноцтой худалдан авагч хандана уу.",
                    'price' => $price,
                    'price_type' => $priceType,
                    'condition' => $cond,
                    'postal_code' => $plz,
                    'city' => $city,
                    'country' => 'Герман',
                    'contact_name' => 'Бат',
                    'contact_phone' => '+49 170 1234567',
                    'contact_email' => 'bat@example.com',
                    'images' => [],
                    'status' => 'active',
                    'is_featured' => $i < 2,
                ]
            );
        }
    }
}
