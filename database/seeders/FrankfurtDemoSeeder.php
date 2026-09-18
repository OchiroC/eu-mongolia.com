<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Event;
use App\Models\Flight;
use App\Models\HousingPost;
use App\Models\KidsResource;
use App\Models\Parcel;
use App\Models\Professional;
use App\Models\ProfessionalCategory;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Франкфуртад төвлөрсөн демо өгөгдөл (нууц үгээр хаалттай туршилтын орчинд).
 * Давтан ажиллуулахад аюулгүй: бичлэг бүрийг slug эсвэл имэйлээр нь олж шинэчилнэ.
 * Бизнесийн хаягт бодит гудамж бичихгүй, зөвхөн дүүргийн нэр, дүүргийн төвийн ойролцоо координат.
 */
class FrankfurtDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([FlightScheduleSeeder::class, JourneyGuidesSeeder::class]);

        $users = $this->users();
        $admin = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first() ?? $users[0];

        $this->businesses($admin);
        $this->professionals($users);
        $this->housing($users);
        $this->kids();
        $this->events($admin);
        $this->flightActivity($users);
        $this->cleanDemoText();
    }

    /** @return array<int, User> */
    private function users(): array
    {
        $people = [
            ['Сарнай Батбаяр', 'sarnai.demo@example.com'],
            ['Тэмүүлэн Ганбаатар', 'temuulen.demo@example.com'],
            ['Ану Дорж', 'anu.demo@example.com'],
            ['Болд Цэрэн', 'bold.demo@example.com'],
            ['Номин Энхбат', 'nomin.demo@example.com'],
            ['Ганзориг Жамц', 'ganzorig.demo@example.com'],
        ];

        return array_map(function ($p) {
            $user = User::firstOrCreate(['email' => $p[1]], [
                'name' => $p[0],
                'password' => bcrypt(Str::random(32)),
                'email_verified_at' => now(),
            ]);
            if (method_exists($user, 'assignRole') && DB::table('roles')->where('name', 'user')->exists() && ! $user->hasRole('user')) {
                $user->assignRole('user');
            }

            return $user;
        }, $people);
    }

    private function businesses(User $owner): void
    {
        $rows = [
            ['Гэр Ресторан', 'restaurant', 'Бууз, хуушуур, цуйван зэрэг монгол хоол. Амралтын өдрүүдэд урьдчилан захиалга авна.', 'Франкфурт', 'Frankfurt-Bahnhofsviertel', 'Да-Ня 11:00-22:00', 50.1072, 8.6647, true],
            ['Нүүдэл Хүнсний дэлгүүр', 'grocery', 'Монгол, ази хүнсний бүтээгдэхүүн, цай, амттан. Германд зөвшөөрөгдсөн бүтээгдэхүүн худалдаална.', 'Оффенбах', 'Offenbach-Innenstadt', 'Да-Бя 10:00-20:00', 50.0956, 8.7761, false],
            ['Саран Гоо сайхны салон', 'beauty', 'Үс засалт, будалт, хумсны үйлчилгээ. Монгол, герман хэлээр үйлчилнэ.', 'Франкфурт', 'Frankfurt-Bockenheim', 'Мя-Бя 10:00-19:00', 50.1213, 8.6441, false],
            ['Хөвсгөл Кафе', 'cafe', 'Сүүтэй цай, боорцог, хуушуур. Ажлын өдрүүдэд өдрийн хоолны цэстэй.', 'Франкфурт', 'Frankfurt-Sachsenhausen', 'Да-Ба 08:00-18:00', 50.1003, 8.6891, false],
            ['Монгол Орчуулгын Товчоо', 'service', 'Бичиг баримтын орчуулга (монгол, герман, англи), албан байгууллагад дагалдан орчуулах.', 'Франкфурт', 'Frankfurt-Nordend', 'Да-Ба 09:00-17:00', 50.1265, 8.6934, false],
        ];

        foreach ($rows as [$name, $cat, $desc, $city, $address, $hours, $lat, $lng, $featured]) {
            Business::updateOrCreate(['slug' => Str::slug($name)], [
                'user_id' => $owner->id, 'name' => $name, 'category' => $cat, 'description' => $desc,
                'city' => $city, 'country' => 'Герман', 'address' => $address, 'hours' => $hours,
                'lat' => $lat, 'lng' => $lng, 'status' => 'active',
                'is_featured' => $featured, 'featured_until' => $featured ? now()->addMonth() : null,
            ]);
        }
    }

    /** @param array<int, User> $users */
    private function professionals(array $users): void
    {
        $rows = [
            ['Номин Энхбат', 'Орчуулагч', 'Орчуулагч (DE, MN)', 'Хотын бүртгэл, эмнэлэг, банкинд дагалдан орчуулна. Бичиг баримтын орчуулга хийнэ.', 'Франкфурт', 50.1150, 8.6700, $users[4]],
            ['Ганзориг Жамц', 'Бухгалтер / Татвар', 'Нягтлан бодогч', 'Жилийн татварын тайлан (Steuererklärung), мини ажил, хувиараа хөдөлмөр эрхлэгчийн бүртгэлд зөвлөнө.', 'Оффенбах', 50.1010, 8.7650, $users[5]],
            ['Ану Дорж', 'Үсчин / Гоо сайхан', 'Үсчин', 'Эмэгтэй, эрэгтэй, хүүхдийн үс засалт. Гэрт очиж үйлчилнэ.', 'Франкфурт', 50.1180, 8.7000, $users[2]],
        ];

        foreach ($rows as [$name, $catName, $profession, $bio, $city, $lat, $lng, $user]) {
            $category = ProfessionalCategory::firstOrCreate(['slug' => Str::slug($catName)], ['name' => $catName]);
            Professional::updateOrCreate(['slug' => Str::slug($name.' '.$profession)], [
                'user_id' => $user->id, 'professional_category_id' => $category->id, 'name' => $name,
                'profession' => $profession, 'bio' => $bio, 'city' => $city, 'country' => 'Герман',
                'languages' => ['Монгол', 'Герман'], 'status' => 'active', 'is_verified' => true,
                'lat' => $lat, 'lng' => $lng,
            ]);
        }
    }

    /** @param array<int, User> $users */
    private function housing(array $users): void
    {
        $rows = [
            ['WG-д нэг өрөө, Франкфурт-Бокенхайм', 'wg', 'Франкфурт', 'Bockenheim', 520, 1040, '1', 16, true, 'any', 'Гурван хүнтэй WG-д тавилгатай өрөө. U-Bahn-ы буудал руу 5 минут. Хотын бүртгэл хийж болно.', $users[0]],
            ['2 өрөө байр, Оффенбах', 'apartment', 'Оффенбах', 'Innenstadt', 890, 2670, '2', 54, false, 'any', 'Гэр бүлд тохиромжтой 2 өрөө байр, гал тогоотой. S-Bahn-аар Франкфуртын төв хүртэл 15 минут.', $users[1]],
            ['Түр байр, 1 сар, Майнц', 'room', 'Майнц', 'Neustadt', 450, 0, '1', 14, true, 'female', 'Шинээр ирсэн эмэгтэйд 1 сарын хугацаатай тавилгатай өрөө. Оршин суух баталгаа өгнө.', $users[2]],
            ['Франкфурт орчимд өрөө хайж байна', 'seeking', 'Франкфурт', null, 600, null, '1', null, false, 'any', '11-р сараас Франкфурт эсвэл Оффенбахад өрөө хайж байна. Хотын бүртгэл хийх боломжтой байх шаардлагатай.', $users[3]],
        ];

        foreach ($rows as [$title, $type, $city, $district, $price, $deposit, $rooms, $size, $furnished, $gender, $desc, $user]) {
            HousingPost::updateOrCreate(['slug' => Str::slug($title)], [
                'user_id' => $user->id, 'title' => $title, 'type' => $type, 'city' => $city, 'country' => 'Герман',
                'district' => $district, 'price' => $price, 'deposit' => $deposit, 'rooms' => $rooms, 'size' => $size,
                'available_from' => now()->addWeeks(2)->toDateString(), 'furnished' => $furnished,
                'gender_pref' => $gender, 'description' => $desc, 'status' => 'active',
            ]);
        }
    }

    private function kids(): void
    {
        $rows = [
            ['Франкфуртын монгол хэлний бямба гарагийн хичээл', 'school', '6-12', 'Бямба гараг бүр 10:00-12:00 монгол бичиг, яриа, дуу.', 'Франкфурт', true],
            ['Хүүхдэд зориулсан монгол үлгэрийн ном', 'books', '3-8', 'Германд өсч буй хүүхдүүдэд зориулсан хоёр хэлтэй үлгэрийн номын жагсаалт.', null, false],
            ['Монгол ардын бүжгийн дугуйлан', 'culture', '7-14', 'Сар бүр хоёр удаа Оффенбахад хичээллэнэ.', 'Оффенбах', false],
        ];

        foreach ($rows as $i => [$title, $cat, $age, $desc, $city, $featured]) {
            KidsResource::updateOrCreate(['title' => $title], [
                'category' => $cat, 'age_range' => $age, 'description' => $desc,
                'city' => $city, 'country' => $city ? 'Герман' : null,
                'is_featured' => $featured, 'sort_order' => $i, 'is_active' => true,
            ]);
        }
    }

    private function events(User $organizer): void
    {
        $rows = [
            ['Франкфуртын монголчуудын намрын уулзалт', 'Шинээр ирсэн болон удаан амьдарч буй монголчуудын танилцах уулзалт. Хотын бүртгэл, ажил, байрны талаар туршлага хуваалцана.', 'Saalbau Bockenheim', 'Франкфурт', '2026-10-10 18:00', true],
            ['Монгол хоолны өдөр, Оффенбах', 'Гэр бүлээрээ ирж монгол хоол амтлах, хүүхдийн тоглоом, дуу хуур.', 'Bürgerhaus Offenbach', 'Оффенбах', '2026-11-14 14:00', false],
        ];

        foreach ($rows as [$title, $desc, $venue, $city, $at, $featured]) {
            $start = \Illuminate\Support\Carbon::parse($at, 'Europe/Berlin');
            Event::updateOrCreate(['slug' => Str::slug($title)], [
                'organizer_id' => $organizer->id, 'title' => $title, 'description' => '<p>'.$desc.'</p>',
                'venue' => $venue, 'city' => $city, 'country' => 'Герман',
                'starts_at' => $start, 'ends_at' => $start->copy()->addHours(4),
                'status' => 'published', 'is_featured' => $featured, 'has_tickets' => false,
            ]);
        }
    }

    /** @param array<int, User> $users */
    private function flightActivity(array $users): void
    {
        $arrivals = Flight::upcoming()->where('direction', 'arrival')->orderBy('scheduled_at')->take(6)->get();
        $departures = Flight::upcoming()->where('direction', 'departure')->orderBy('scheduled_at')->take(4)->get();

        // Нислэгээр ирэх, явах хүмүүс.
        foreach ($arrivals as $i => $flight) {
            $flight->passengers()->syncWithoutDetaching(collect($users)->slice($i % 3, 2 + $i % 3)->pluck('id')->all());
        }
        foreach ($departures as $i => $flight) {
            $flight->passengers()->syncWithoutDetaching([$users[($i + 4) % 6]->id]);
        }

        // Нисэх буудлаас хот руу, хотоос нисэх буудал руу машин.
        $toCity = ['Франкфурт, төв буудал', 'Майнц', 'Дармштадт', 'Висбаден'];
        foreach ($arrivals->take(4) as $i => $flight) {
            $local = $flight->localTime();
            Ride::firstOrCreate(['flight_id' => $flight->id, 'user_id' => $users[$i]->id], [
                'from_city' => 'Франкфурт нисэх буудал', 'from_country' => 'Герман',
                'to_city' => $toCity[$i], 'to_country' => 'Герман',
                'depart_at' => $local->copy()->addMinutes(75)->format('Y-m-d H:i:s'),
                'seats' => 3 - ($i % 2), 'price' => $i === 0 ? 'Үнэгүй' : ($i + 1) * 5 .' €',
                'notes' => 'Терминал 3-ын гарцын гадна хүлээнэ. Ачаа ихтэй бол урьдчилан хэлээрэй.', 'status' => 'active',
            ]);
        }
        foreach ($departures->take(2) as $i => $flight) {
            $local = $flight->localTime();
            Ride::firstOrCreate(['flight_id' => $flight->id, 'user_id' => $users[$i + 4]->id], [
                'from_city' => $i === 0 ? 'Оффенбах' : 'Майнц', 'from_country' => 'Герман',
                'to_city' => 'Франкфурт нисэх буудал', 'to_country' => 'Герман',
                'depart_at' => $local->copy()->subHours(4)->format('Y-m-d H:i:s'),
                'seats' => 2, 'price' => '10 €', 'notes' => 'Терминал 3 хүртэл хүргэнэ.', 'status' => 'active',
            ]);
        }

        // Ачаа, илгээмж.
        $parcels = [
            [$arrivals[0] ?? null, 'offer', 'to_germany', 'Улаанбаатар', 'Франкфурт', 5, '5 €/кг', 'Ачаанд 5 кг сул зай байна. Ном, хувцас зэрэг хуурай зүйл авч явна. Хүнсний бүтээгдэхүүн авахгүй.', 0],
            [$arrivals[1] ?? null, 'request', 'to_germany', 'Улаанбаатар', 'Майнц', 2, 'Тохиролцоно', 'Ээжийн явуулсан 2 кг ном, хувцас авчрах хүн хэрэгтэй. Улаанбаатарт хүлээлгэж өгнө.', 1],
            [$arrivals[2] ?? null, 'offer', 'to_germany', 'Улаанбаатар', 'Оффенбах', 8, '4 €/кг', 'Ачаанд 8 кг зай байна. Нисэхээс өмнө ачааг шалгаж хүлээж авна.', 2],
            [$departures[0] ?? null, 'offer', 'to_mongolia', 'Франкфурт', 'Улаанбаатар', 6, '6 €/кг', 'Улаанбаатар руу нисэж байна. Эм, хувцас, бичиг баримт авч явна.', 3],
            [$departures[1] ?? null, 'request', 'to_mongolia', 'Дармштадт', 'Улаанбаатар', 3, 'Тохиролцоно', 'Эмээдээ 3 кг бэлэг явуулмаар байна. Франкфуртын нисэх буудал дээр хүлээлгэж өгнө.', 4],
        ];
        foreach ($parcels as [$flight, $type, $dir, $from, $to, $kg, $price, $desc, $u]) {
            if (! $flight) {
                continue;
            }
            Parcel::firstOrCreate(['flight_id' => $flight->id, 'user_id' => $users[$u]->id, 'type' => $type], [
                'direction' => $dir, 'from_city' => $from, 'to_city' => $to, 'weight_kg' => $kg,
                'price' => $price, 'description' => $desc, 'status' => 'active',
            ]);
        }
    }

    /** Хуучин демо өгөгдлөөс "(demo)" тэмдэглэгээ, урт, дунд зураасыг цэвэрлэнэ. */
    private function cleanDemoText(): void
    {
        $targets = [
            'guides' => ['title', 'excerpt', 'body'], 'posts' => ['title', 'excerpt', 'body'],
            'events' => ['title', 'description'], 'listings' => ['title', 'description'],
            'businesses' => ['name', 'description', 'hours'], 'professionals' => ['bio', 'profession'],
            'job_posts' => ['title', 'description'], 'housing_posts' => ['title', 'description'],
            'questions' => ['title', 'body'], 'answers' => ['body'], 'comments' => ['body'],
            'rides' => ['notes'], 'embassies' => ['notes'], 'kids_resources' => ['title', 'description'],
        ];
        foreach ($targets as $table => $cols) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach ($cols as $col) {
                if (! Schema::hasColumn($table, $col)) {
                    continue;
                }
                DB::table($table)
                    ->where(fn ($q) => $q->where($col, 'like', '%(demo%')->orWhere($col, 'like', '%—%')->orWhere($col, 'like', '%–%')->orWhere($col, 'like', '% vs %'))
                    ->update([$col => DB::raw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`$col`, ' (demo хариулт)', ''), ' (demo)', ''), '(demo)', ''), ' — ', ', '), '—', '-'), '–', '-'), ' vs ', ' эсвэл ')")]);
            }
        }
    }
}
