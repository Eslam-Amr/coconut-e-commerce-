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
            PermissionSeeder::class,
            RoleSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            AttributeSeeder::class,
            AttributeValueSeeder::class,
            ProductSeeder::class,
            ProductAttributeSeeder::class,
            ProductVariantSeeder::class,
            VariantAttributeValueSeeder::class,
            AddressSeeder::class,
            StaticPageSeeder::class,
            BannerSeeder::class,
            SliderSeeder::class,
            FlashSaleSeeder::class,
            VoucherSeeder::class,
        ]);

        User::updateOrCreate(
            ['email' => 'test@example.com'], // search by email
            [
                'name' => 'Test User',
                'phone' => '+10000000001',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        $admin = Admin::updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'email' => 'admin@example.com',
            'name' => 'Admin',
            'phone' => '+10000000002',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        // Assign super_admin role to the admin user
        $admin->assignRole('super_admin');
    }
}
