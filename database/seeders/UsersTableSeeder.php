<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Faker\Factory as Faker;
use App\Models\User;
use App\Models\City;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // 1) Create a known admin user
        User::create([
            'name'              => 'Monther Hamdan',
            'phone'             => '0785452835',
            'city'              => City::inRandomOrder()->value('id'),
            'password'          => Hash::make('password123'),
            'otp_code'          => null,
            'otp_expires_at'    => null,
            'security_token'    => Str::random(60),
            'phone_verified_at' => Carbon::now(),
        ]);

        $this->command->info('Users seeded: 1 admin ');
    }
}
