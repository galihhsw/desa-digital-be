<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gunan.id',
            'password' => bcrypt('admin123'),
        ])->assignRole('admin');

        User::create([
            'name' => 'Kepala Keluarga',
            'email' => 'headOfFamily@gunan.id',
            'password' => bcrypt('kepala123'),
        ])->assignRole('head-of-family');

        UserFactory::new()->count(15)->create();
    }
}
