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
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        ##======== CREATION DES ROLES PAR DEFAUT ============####

        \App\Models\Role::factory()->create([
            'role'=>'is_transporter'
        ]);

        \App\Models\Role::factory()->create([
            'role'=>'is_sender'
        ]);
        \App\Models\Role::factory()->create([
            'role'=>'is_admin'
        ]);
        \App\Models\Role::factory()->create([
            'role'=>'is_supervisor'
        ]);
        \App\Models\Role::factory()->create([
            'role'=>'is_shipper'
        ]);
        \App\Models\Role::factory()->create([
            'role'=>'is_biller'
        ]);


         ##======== CREATION DES TYPES DE MOYEN DE TRANSPORT PAR DEFAUT ============####

         \App\Models\Type::factory(10)->create();
    }
}
