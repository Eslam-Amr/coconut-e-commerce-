<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Active vouchers with different discount types
        $vouchers = [
            [
                'code' => 'WELCOME10',
                'discount' => 10.00,
                'usage_limit' => 100,
                'usage_limit_per_user' => 1,
                'used_count' => 15,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(30),
                'active' => true,
            ],
            [
                'code' => 'SAVE20',
                'discount' => 20.00,
                'usage_limit' => 50,
                'usage_limit_per_user' => 2,
                'used_count' => 8,
                'start_date' => now()->subDays(2),
                'end_date' => now()->addDays(15),
                'active' => true,
            ],
            [
                'code' => 'MEGA30',
                'discount' => 30.00,
                'usage_limit' => 25,
                'usage_limit_per_user' => 1,
                'used_count' => 3,
                'start_date' => now(),
                'end_date' => now()->addDays(7),
                'active' => true,
            ],
            [
                'code' => 'FIRST50',
                'discount' => 50.00,
                'usage_limit' => 10,
                'usage_limit_per_user' => 1,
                'used_count' => 0,
                'start_date' => now(),
                'end_date' => now()->addDays(3),
                'active' => true,
            ],
            [
                'code' => 'LOYAL15',
                'discount' => 15.00,
                'usage_limit' => null, // Unlimited usage
                'usage_limit_per_user' => 3,
                'used_count' => 45,
                'start_date' => now()->subDays(10),
                'end_date' => now()->addDays(60),
                'active' => true,
            ],
            [
                'code' => 'BULK25',
                'discount' => 25.00,
                'usage_limit' => 200,
                'usage_limit_per_user' => 5,
                'used_count' => 67,
                'start_date' => now()->subDays(1),
                'end_date' => now()->addDays(20),
                'active' => true,
            ],
            [
                'code' => 'FLASH40',
                'discount' => 40.00,
                'usage_limit' => 15,
                'usage_limit_per_user' => 1,
                'used_count' => 0,
                'start_date' => now()->addHours(2),
                'end_date' => now()->addDays(1),
                'active' => true,
            ],
            [
                'code' => 'STUDENT20',
                'discount' => 20.00,
                'usage_limit' => 500,
                'usage_limit_per_user' => 2,
                'used_count' => 123,
                'start_date' => now()->subDays(7),
                'end_date' => now()->addDays(45),
                'active' => true,
            ],
        ];

        // Create vouchers
        foreach ($vouchers as $voucherData) {
            Voucher::create($voucherData);
        }

        // Create some expired vouchers for testing
        $expiredVouchers = [
            [
                'code' => 'EXPIRED10',
                'discount' => 10.00,
                'usage_limit' => 50,
                'usage_limit_per_user' => 1,
                'used_count' => 12,
                'start_date' => now()->subDays(20),
                'end_date' => now()->subDays(5),
                'active' => false,
            ],
            [
                'code' => 'OLD20',
                'discount' => 20.00,
                'usage_limit' => 30,
                'usage_limit_per_user' => 1,
                'used_count' => 30, // Fully used
                'start_date' => now()->subDays(15),
                'end_date' => now()->subDays(2),
                'active' => false,
            ],
        ];

        foreach ($expiredVouchers as $voucherData) {
            Voucher::create($voucherData);
        }

        // Create some future vouchers
        $futureVouchers = [
            [
                'code' => 'FUTURE25',
                'discount' => 25.00,
                'usage_limit' => 100,
                'usage_limit_per_user' => 2,
                'used_count' => 0,
                'start_date' => now()->addDays(3),
                'end_date' => now()->addDays(20),
                'active' => true,
            ],
            [
                'code' => 'HOLIDAY35',
                'discount' => 35.00,
                'usage_limit' => 75,
                'usage_limit_per_user' => 1,
                'used_count' => 0,
                'start_date' => now()->addDays(7),
                'end_date' => now()->addDays(14),
                'active' => true,
            ],
        ];

        foreach ($futureVouchers as $voucherData) {
            Voucher::create($voucherData);
        }

        // Create some inactive vouchers
        $inactiveVouchers = [
            [
                'code' => 'INACTIVE15',
                'discount' => 15.00,
                'usage_limit' => 50,
                'usage_limit_per_user' => 1,
                'used_count' => 0,
                'start_date' => now()->subDays(1),
                'end_date' => now()->addDays(10),
                'active' => false,
            ],
        ];

        foreach ($inactiveVouchers as $voucherData) {
            Voucher::create($voucherData);
        }
    }
}
