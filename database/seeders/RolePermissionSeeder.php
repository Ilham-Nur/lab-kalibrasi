<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'hr.view',
            'hr.create',
            'hr.edit',
            'hr.delete',
            'documents.view',
            'documents.create',
            'documents.edit',
            'documents.delete',
            'assets.view',
            'assets.create',
            'assets.edit',
            'assets.delete',
            'asset-procurements.view',
            'asset-procurements.create',
            'asset-procurements.edit',
            'asset-procurements.delete',
            'asset-procurements.approve',
            'asset-suppliers.view',
            'asset-suppliers.create',
            'asset-suppliers.edit',
            'asset-suppliers.delete',
            'asset-receipts.view',
            'asset-receipts.create',
            'asset-receipts.edit',
            'asset-receipts.delete',
            'asset-conversions.view',
            'asset-conversions.create',
            'asset-inspections.view',
            'asset-inspections.create',
            'asset-inspections.edit',
            'asset-inspections.delete',
            'asset-calibrations.view',
            'asset-calibrations.create',
            'asset-calibrations.edit',
            'asset-calibrations.delete',
            'asset-reports.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $admin = Role::findOrCreate('Admin', 'web');
        $admin->syncPermissions($permissions);

        Role::findOrCreate('Supervisor', 'web')->syncPermissions([
            'dashboard.view',
            'assets.view',
            'asset-procurements.view',
            'asset-procurements.approve',
            'asset-reports.view',
        ]);

        Role::findOrCreate('Keuangan', 'web')->syncPermissions([
            'dashboard.view',
            'assets.view',
            'asset-procurements.view',
            'asset-procurements.approve',
            'asset-reports.view',
        ]);

        Role::findOrCreate('Direktur', 'web')->syncPermissions([
            'dashboard.view',
            'assets.view',
            'asset-procurements.view',
            'asset-procurements.approve',
            'asset-reports.view',
        ]);

        Role::findOrCreate('Staff Pengadaan', 'web')->syncPermissions([
            'dashboard.view',
            'assets.view',
            'asset-procurements.view',
            'asset-procurements.create',
            'asset-procurements.edit',
            'asset-suppliers.view',
            'asset-suppliers.create',
            'asset-suppliers.edit',
            'asset-receipts.view',
            'asset-receipts.create',
            'asset-receipts.edit',
            'asset-conversions.view',
            'asset-conversions.create',
        ]);

        Role::findOrCreate('Staff Lab', 'web')->syncPermissions([
            'dashboard.view',
            'assets.view',
            'assets.create',
            'assets.edit',
            'asset-inspections.view',
            'asset-inspections.create',
            'asset-inspections.edit',
            'asset-calibrations.view',
            'asset-reports.view',
        ]);

        Role::findOrCreate('Teknisi Kalibrasi', 'web')->syncPermissions([
            'dashboard.view',
            'assets.view',
            'asset-calibrations.view',
            'asset-calibrations.create',
            'asset-calibrations.edit',
            'asset-reports.view',
        ]);

        Role::findOrCreate('Viewer', 'web')->syncPermissions([
            'dashboard.view',
            'assets.view',
            'asset-suppliers.view',
            'asset-procurements.view',
            'asset-receipts.view',
            'asset-inspections.view',
            'asset-calibrations.view',
            'asset-reports.view',
        ]);

        User::where('username', 'admin')->first()?->assignRole('Admin');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
