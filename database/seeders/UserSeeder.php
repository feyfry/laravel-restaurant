<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::insert([
            [
                'name' => 'Mickala Fey',
                'email' => 'feyfeyfry@gmail.com',
                'email_verified_at' => now(),
                'password' => bcrypt('password123'),
                'role' => 'admin',

            ],
            [
                'name' => 'Faiz',
                'email' => 'feyfeifry@gmail.com',
                'email_verified_at' => now(),
                'password' => bcrypt('password123'),
                'role' => 'operator',

            ],
        ]);
    }
}
