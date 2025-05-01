<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SystemAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        do {
            $uuid = strtolower(str_replace('-', '', Str::uuid()->toString()));
            $code = substr($uuid, 0, 10);
        } while (User::query()->where('code', $code)->exists());

        $admin = User::create([
            'code' => $code,
            'name' => 'System Admin',
            'email' => 'coursemely@gmail.com',
            'password' => Hash::make('Coursemely@gmail.com'),
            'email_verified_at' => now(),
            'thumbnail' => 'https://res.cloudinary.com/dvrexlsgx/image/upload/v1746133764/Gemini_Generated_Image_6g1crc6g1crc6g1c_abpz3g.jpg',
            'status' => User::STATUS_ACTIVE,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $admin->assignRole('admin');
    }
}
