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
        $users = [
            [
                'firstname' => 'Admin ',
                'lastname' => 'admin 1',
                'username' => 'admin',
                'email' => 'admin@gmail.com',
                'password' => '$2y$10$CI5P59ICr/HOihqlnYUrLeKwCajgMKd34HB66.JsJBrIOQY9fazrG', #admin
                'phone' => "22961765591",
                'is_admin' => true,
            ],
            [
                'firstname' => 'PP JJOEL',
                'lastname' => 'admin 2',
                'username' => 'ppjjoel',
                'email' => 'ppjjoel@gmail.com',
                'password' => '$2y$10$ZT2msbcfYEUWGUucpnrHwekWMBDe1H0zGrvB.pzQGpepF8zoaGIMC', #ppjjoel
                'phone' => "22961765592",
                'is_admin' => true,
            ]
        ];

        foreach ($users as $user) {
            \App\Models\User::factory()->create($user);
        }

        // \App\Models\User::factory()->create([
        //     'username' => 'admin',
        //     'email' => 'admin@gmail.com',
        //     'password' => '$2y$10$CI5P59ICr/HOihqlnYUrLeKwCajgMKd34HB66.JsJBrIOQY9fazrG',
        //     'is_admin' => true
        // ]);
    }
}
