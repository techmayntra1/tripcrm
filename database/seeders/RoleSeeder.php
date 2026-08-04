<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'Full access to all features. This role cannot be deleted.',
                'is_default' => true,
            ]
        );

        Role::firstOrCreate(
            ['slug' => 'subadmin'],
            [
                'name' => 'Sub Admin',
                'description' => 'Limited administrative access.',
                'is_default' => false,
            ]
        );
    }
}
