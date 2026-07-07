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
            'manage coordinators',
            'create coordinators',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // create roles and assign created permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Administrator']);
        $superAdmin->givePermissionTo(Permission::all());

        $nationalAdmin = Role::firstOrCreate(['name' => 'National Administrator']);
        $nationalAdmin->givePermissionTo([
            'view members', 'create members', 'edit members', 'verify members',
            'view esco', 'manage esco',
            'view announcements', 'create announcements',
            'manage coordinators', 'create coordinators',
        ]);

        $stateCoordinator = Role::firstOrCreate(['name' => 'State Coordinator']);
        $stateCoordinator->givePermissionTo([
            'view members', 'create members', 'edit members', 'verify members',
            'view esco', 'manage esco', 'view announcements', 'create announcements',
            'manage coordinators', 'create coordinators',
        ]);

        $lgaCoordinator = Role::firstOrCreate(['name' => 'LGA Coordinator']);
        $lgaCoordinator->givePermissionTo([
            'view members', 'create members', 'edit members', 'verify members',
            'view esco', 'manage esco', 'view announcements', 'create announcements',
            'manage coordinators', 'create coordinators',
        ]);

        $wardCoordinator = Role::firstOrCreate(['name' => 'Ward Coordinator']);
        $wardCoordinator->givePermissionTo([
            'view members', 'create members', 'edit members', 'verify members',
            'view esco', 'manage esco', 'view announcements',
            'manage coordinators', 'create coordinators',
        ]);

        $pollingUnitCoordinator = Role::firstOrCreate(['name' => 'Polling Unit Coordinator']);
        $pollingUnitCoordinator->givePermissionTo([
            'view members', 'create members', 'edit members', 'verify members',
            'view esco', 'view announcements',
        ]);
    }
}
