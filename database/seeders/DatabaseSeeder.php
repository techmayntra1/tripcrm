<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
        ]);

        $adminRole = Role::where('slug', 'admin')->first();

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@tripmantra.com',
            'password' => bcrypt('12345678'),
            'role_id' => $adminRole?->id,
            'is_active' => true,
        ]);
    }
}
