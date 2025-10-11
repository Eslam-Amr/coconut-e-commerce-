<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();


        $this->call([
            CategorySeeder::class,
            AttributeSeeder::class,
            AttributeValueSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+10000000001',
        ]);
        Admin::updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'email' => 'admin@example.com',
            'name' => 'Admin',
            'phone' => '+10000000002',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);
    }
}
