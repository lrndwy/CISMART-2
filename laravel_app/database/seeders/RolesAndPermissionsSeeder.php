<?php

namespace Database\Seeders;

use App\Models\User;
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

        // Create demo users

        // Admin user
        $lauraSari = User::firstOrCreate(
            ['email' => 'laurasari@pnc.ac.id'],
            [
                'name' => 'Laura Sari',
                'password' => bcrypt('password'),
                'phone' => '+6281234567895',
                'address' => 'Jl. Pendidikan No. 30, Cilacap',
                'email_verified_at' => now(),
            ]
        );
        $lauraSari->assignRole('admin');

        $admin = User::firstOrCreate(
            ['email' => 'admin@cismart.test'],
            [
                'name' => 'Admin CISMART',
                'password' => bcrypt('password'),
                'phone' => '+6281234567890',
                'address' => 'Jl. Admin No. 1, Cilacap',
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole(roles: 'admin');

        // Seller users
        $seller1 = User::firstOrCreate(
            ['email' => 'seller1@cismart.test'],
            [
                'name' => 'Sari Handmade',
                'password' => bcrypt('password'),
                'phone' => '+6281234567891',
                'address' => 'Jl. Kerajinan No. 10, Cilacap',
                'email_verified_at' => now(),
            ]
        );
        $seller1->assignRole('seller');

        $seller2 = User::firstOrCreate(
            ['email' => 'seller2@cismart.test'],
            [
                'name' => 'Budi Kuliner',
                'password' => bcrypt('password'),
                'phone' => '+6281234567892',
                'address' => 'Jl. Makanan No. 15, Cilacap',
                'email_verified_at' => now(),
            ]
        );
        $seller2->assignRole('seller');

        // Buyer users
        $buyer1 = User::firstOrCreate(
            ['email' => 'buyer1@cismart.test'],
            [
                'name' => 'Andi Pembeli',
                'password' => bcrypt('password'),
                'phone' => '+6281234567893',
                'address' => 'Jl. Konsumen No. 20, Jakarta',
                'email_verified_at' => now(),
            ]
        );
        $buyer1->assignRole('buyer');

        $buyer2 = User::firstOrCreate(
            ['email' => 'buyer2@cismart.test'],
            [
                'name' => 'Rina Shopper',
                'password' => bcrypt('password'),
                'phone' => '+6281234567894',
                'address' => 'Jl. Belanja No. 25, Bandung',
                'email_verified_at' => now(),
            ]
        );
        $buyer2->assignRole('buyer');
    }
}
