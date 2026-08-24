<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class CreateRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(
            ['name' => User::ROLE_SUPER_USER],
            ['description' => 'Super Usuario']
        );
        Role::firstOrCreate(
            ['name' => User::ROLE_ADMIN],
            ['description' => 'Administrador']
        );
        Role::firstOrCreate(
            ['name' => User::ROLE_USER],
            ['description' => 'Usuario']
        );
    }
}
