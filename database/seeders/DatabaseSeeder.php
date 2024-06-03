<?php

namespace Database\Seeders;

use App\Models\User;
use Faker\Provider\Uuid;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'uuid' => Uuid::uuid(),
            'name' => 'SUPER ADMIN',
            'email' => 'superadmin@gmail.com',
            'password' => bcrypt('rahasia'),
            'phone' => '-',
            'role' => 'SUPER-ADMIN'
        ]);
    }
}
