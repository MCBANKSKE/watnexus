<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::firstOrCreate(['name' => 'super_admin']);

        $user = User::firstOrCreate(
            ['email' => 'mcbankske@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_superadmin' => false,
            ]
        );

        if (!$user->hasRole('super_admin')) {
            $user->assignRole($role);
        }
    }
}
