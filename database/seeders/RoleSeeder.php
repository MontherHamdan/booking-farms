<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create or get existing roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $farmOwnerRole = Role::firstOrCreate(['name' => 'farm_owner']);

        // Create Super Admin User (only if doesn't exist)
        $superAdmin = User::firstOrCreate(
            ['phone' => '0000000000'],
            [
                'name' => 'Super Admin',
                'email' => 'admin@farmplatform.com',
                'password' => Hash::make('admin123456'),
                'status' => 'active',
                'phone_verified_at' => now(),
            ]
        );

        // Assign super_admin role if not already assigned
        if (!$superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole('super_admin');
        }
    }
}