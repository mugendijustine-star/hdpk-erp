<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'pos.view',
            'pos.create',
            'pos.approve',
            'stock.view',
            'stock.adjust',
            'purchases.view',
            'purchases.create',
            'purchases.approve',
            'manufacturing.view',
            'manufacturing.record',
            'manufacturing.approve',
            'hr.view',
            'hr.payroll.run',
            'hr.payroll.approve',
            'reports.view.sales',
            'reports.view.inventory',
            'reports.view.manufacturing',
            'reports.view.hr',
            'reports.view.accounting',
            'reports.view.audit',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $clerkRole = Role::firstOrCreate(['name' => 'clerk', 'guard_name' => 'web']);

        $adminRole->givePermissionTo($permissions);

        $managerPermissions = [
            'pos.view',
            'pos.create',
            'pos.approve',
            'stock.view',
            'stock.adjust',
            'purchases.view',
            'purchases.create',
            'purchases.approve',
            'manufacturing.view',
            'manufacturing.record',
            'manufacturing.approve',
            'hr.view',
            'hr.payroll.run',
            'hr.payroll.approve',
            'reports.view.sales',
            'reports.view.inventory',
            'reports.view.manufacturing',
            'reports.view.hr',
            'reports.view.accounting',
        ];
        $managerRole->givePermissionTo($managerPermissions);

        $clerkPermissions = [
            'pos.view',
            'pos.create',
            'stock.view',
            'purchases.view',
            'purchases.create',
            'manufacturing.view',
            'manufacturing.record',
            'hr.view',
            'reports.view.sales',
            'reports.view.inventory',
            'reports.view.manufacturing',
        ];
        $clerkRole->givePermissionTo($clerkPermissions);
    }
}
