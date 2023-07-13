<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory()->create([
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => '$2y$10$CI5P59ICr/HOihqlnYUrLeKwCajgMKd34HB66.JsJBrIOQY9fazrG',
            'is_admin'=>true
        ]);
    }
}
