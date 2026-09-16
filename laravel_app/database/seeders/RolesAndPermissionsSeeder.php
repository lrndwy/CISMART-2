<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Product management
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'products.manage', // for admin - can manage all products

            // Order management
            'orders.view',
            'orders.create',
            'orders.edit',
            'orders.delete',
            'orders.manage', // for admin - can manage all orders

            // Review management
            'reviews.view',
            'reviews.create',
            'reviews.edit',
            'reviews.delete',
            'reviews.manage', // for admin - can manage all reviews

            // User management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.manage',

            // Seller application management
            'seller-applications.view',
            'seller-applications.manage',

            // Dashboard access
            'dashboard.admin',
            'dashboard.seller',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Admin role - full access
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Seller role - limited access
        $sellerRole = Role::firstOrCreate(['name' => 'seller']);
        $sellerRole->givePermissionTo([
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'orders.view',
            'orders.edit',
            'reviews.view',
            'dashboard.seller',
        ]);

        // Buyer role - very limited access
        $buyerRole = Role::firstOrCreate(['name' => 'buyer']);
        $buyerRole->givePermissionTo([
            'products.view',
            'orders.view',
            'orders.create',
            'reviews.view',
            'reviews.create',
        ]);
    }
}
