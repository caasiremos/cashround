<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionFeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = ['MTN', 'Airtel'];
        $transactionTypes = ['deposit', 'withdrawal'];

        $feeTiers = [
            ['min_amount' => 0, 'max_amount' => 50_000, 'provider_fee' => 600],
            ['min_amount' => 50_001, 'max_amount' => 400_000, 'provider_fee' => 900],
            ['min_amount' => 400_001, 'max_amount' => 1_000_000, 'provider_fee' => 1_300],
            ['min_amount' => 1_000_001, 'max_amount' => 999_999_999.9999, 'provider_fee' => 1_500],
        ];

        $rows = [];
        foreach ($providers as $provider) {
            foreach ($transactionTypes as $transactionType) {
                foreach ($feeTiers as $tier) {
                    $rows[] = [
                        'provider' => $provider,
                        'min_amount' => $tier['min_amount'],
                        'max_amount' => $tier['max_amount'],
                        'provider_fee' => $tier['provider_fee'],
                        'service_fee' => $tier['provider_fee'] * 2,
                        'transaction_type' => $transactionType,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        DB::table('transaction_fees')->insert($rows);
    }
}
