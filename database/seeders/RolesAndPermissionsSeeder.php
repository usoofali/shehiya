<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $permissions = [
            'view members',
            'create members',
            'edit members',
            'delete members',
            'verify members',
            'view esco',
            'manage esco',
            'view announcements',
            'create announcements',
            'delete announcements',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // create roles and assign created permissions
        $superAdmin = Role::create(['name' => 'Super Administrator']);
        $superAdmin->givePermissionTo(Permission::all());

        $nationalAdmin = Role::create(['name' => 'National Administrator']);
        $nationalAdmin->givePermissionTo([
            'view members', 'create members', 'edit members', 'verify members',
            'view esco', 'manage esco',
            'view announcements', 'create announcements',
        ]);

        $stateCoordinator = Role::create(['name' => 'State Coordinator']);
        $stateCoordinator->givePermissionTo([
            'view members', 'create members', 'edit members', 'verify members',
            'view esco', 'view announcements', 'create announcements',
        ]);

        $lgaCoordinator = Role::create(['name' => 'LGA Coordinator']);
        $lgaCoordinator->givePermissionTo([
            'view members', 'create members', 'edit members', 'verify members',
            'view esco', 'view announcements', 'create announcements',
        ]);

        $wardCoordinator = Role::create(['name' => 'Ward Coordinator']);
        $wardCoordinator->givePermissionTo([
            'view members', 'create members', 'edit members', 'verify members',
            'view esco', 'view announcements',
        ]);
    }
}
