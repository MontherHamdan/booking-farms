<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Faker\Factory as Faker;
use Illuminate\Http\File;
use App\Models\User;
use App\Models\City;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // ────────────────────────────────────────────────────────────────────
        // 1) Create a known admin user and upload user1.jpg as avatar
        $admin = User::create([
            'name'              => 'Monther Hamdan',
            'phone'             => '0785452835',
            'city'              => City::inRandomOrder()->value('id'),
            'password'          => Hash::make('password123'),
            'otp_code'          => null,
            'otp_expires_at'    => null,
            'security_token'    => Str::random(60),
            'phone_verified_at' => Carbon::now(),
            'status'            => 'active',
        ]);

        // Path to local avatar file for admin
        $localAdminAvatar = storage_path('app/users/user1.jpg');

        if (file_exists($localAdminAvatar)) {
            // Build a filename for S3: e.g. "user-{id}-{slug}-{timestamp}.jpg"
            $slug     = Str::slug($admin->name);
            $ext      = pathinfo($localAdminAvatar, PATHINFO_EXTENSION);
            $filename = "user-{$admin->id}-{$slug}-" . time() . ".{$ext}";

            // Upload to S3 under "avatars/" folder
            $path = Storage::disk('s3')->putFileAs(
                'avatars',
                new File($localAdminAvatar),
                $filename
            );

            // Get the public URL and save in the user record
            $admin->avatar = Storage::disk('s3')->url($path);
            $admin->save();
        }

        // ────────────────────────────────────────────────────────────────────
        // 2) Generate 10 random users, each with its own avatar (user2.jpg → user11.jpg)
        $localAvatarDir = storage_path('app/users/');
        for ($i = 0; $i < 9; $i++) {
            $phone = $faker->numerify('07########');
            $user = User::create([
                'name'              => $faker->name,
                'phone'             => $phone,
                'city'              => City::inRandomOrder()->value('id'),
                'password'          => Hash::make('password'),
                'otp_code'          => null,
                'otp_expires_at'    => null,
                'security_token'    => null,
                'phone_verified_at' => $faker->boolean(80) ? Carbon::now() : null,
                'email_verified_at' => $faker->boolean(80) ? Carbon::now() : null,
                'status'            => 'active',
            ]);

            // Pick a local file: user2.jpg, user3.jpg, … wrapping at user10.jpg
            $avatarIndex = ($i + 2); // i=0 → user2.jpg, …, i=8 → user10.jpg, i=9 → user11.jpg (wrap to 1)
            if ($avatarIndex > 10) {
                $avatarIndex = $avatarIndex % 10; // so user11.jpg → user1.jpg
                if ($avatarIndex === 0) {
                    $avatarIndex = 10;
                }
            }

            $localAvatarPath = $localAvatarDir . "user{$avatarIndex}.jpg";
            if (file_exists($localAvatarPath)) {
                $slug     = Str::slug($user->name);
                $ext      = pathinfo($localAvatarPath, PATHINFO_EXTENSION);
                $filename = "user-{$user->id}-{$slug}-" . time() . ".{$ext}";

                $path = Storage::disk('s3')->putFileAs(
                    'avatars',
                    new File($localAvatarPath),
                    $filename
                );

                $user->avatar = Storage::disk('s3')->url($path);
                $user->save();
            }
        }

        $this->command->info('Users seeded: 1 admin + 10 random (with avatars).');
    }
}
