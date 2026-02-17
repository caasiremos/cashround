<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Wallet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $member = Member::create([
            'first_name' => 'Revenue',
            'last_name' => 'Wallet',
            'email' => 'revenue@temsoft.com',
            'phone_number' => '0782888634',
            'country' => 'Uganda',
            'password' => Hash::make('password'),
        ]);
        $member->wallet()->create();
    }
}
