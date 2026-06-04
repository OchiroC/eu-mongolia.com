<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Дүрүүд болон үндсэн админ хэрэглэгчийг үүсгэнэ.
     */
    public function run(): void
    {
        // Дүрүүд: admin = бүх эрх, editor = сэтгүүлч (мэдээ/Guide),
        // moderator = сэтгэгдэл/гомдол/асуулт, organizer = эвент,
        // advertiser = баннер, user = энгийн гишүүн.
        foreach (['admin', 'editor', 'moderator', 'organizer', 'advertiser', 'user'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Үндсэн админ хэрэглэгч.
        $admin = User::firstOrCreate(
            ['email' => 'admin@eu-mongolia.test'],
            ['name' => 'Админ', 'password' => Hash::make('password')]
        );
        $admin->assignRole('admin');
    }
}
