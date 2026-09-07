<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = array_keys(Permission::getModules());
        $actions = array_keys(Permission::getActions());

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'module' => $module,
                    'action' => $action,
                ]);
            }
        }
    }
}
