<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

class RoleAndUserSeeder extends Seeder
{
    public function run()
    {
        // Pastikan permissions ter-generate oleh Shield
        Artisan::call('shield:generate --all');

        $roles = [
            'super_admin' => 'Super Admin',
            'owner' => 'Owner',
            'marketing' => 'Marketing',
            'finance' => 'Finance',
            'logistics' => 'Warehouse / Logistik',
            'cs' => 'Customer Service',
        ];

        foreach ($roles as $code => $name) {
            Role::firstOrCreate(['name' => $code], ['guard_name' => 'web']);
        }

        // Definisi Permissions untuk masing-masing role (Super Admin sudah punya semua by Shield)
        
        $rolePermissions = [
            'owner' => [
                'view_any_order', 'view_order',
                'view_any_product', 'view_product',
                'view_any_user', 'view_user',
                'view_any_cashflow', 'view_cashflow',
                'widget_OrderStatsWidget', 'widget_RevenueChart',
            ],
            'marketing' => [
                'view_any_product', 'view_product', 'create_product', 'update_product',
                'view_any_category', 'view_category', 'create_category', 'update_category',
                'view_any_voucher', 'view_voucher', 'create_voucher', 'update_voucher',
                'view_any_promo_banner', 'view_promo_banner', 'create_promo_banner', 'update_promo_banner',
                'view_any_sales_page', 'view_sales_page', 'create_sales_page', 'update_sales_page',
            ],
            'finance' => [
                'view_any_order', 'view_order', 'update_order',
                'view_any_cashflow', 'view_cashflow', 'create_cashflow', 'update_cashflow',
                'view_any_payment_method', 'view_payment_method',
                'widget_RevenueChart',
            ],
            'logistics' => [
                'view_any_order', 'view_order', 'update_order',
                'view_any_product', 'view_product', 'update_product', // for stock update
                'view_any_stock_log', 'view_stock_log', 'create_stock_log',
                'view_any_shipping_method', 'view_shipping_method',
            ],
            'cs' => [
                'view_any_inquiry', 'view_inquiry', 'update_inquiry',
                'view_any_product_review', 'view_product_review', 'update_product_review',
                'view_any_product', 'view_product', // Read-only for product info
                'view_any_order', 'view_order', // Check order status for customer
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                // Berikan permissions jika exist di database
                $validPerms = Permission::whereIn('name', $perms)->pluck('name')->toArray();
                $role->syncPermissions($validPerms);
            }
        }

        // --- BIKIN AKUN DEMO ---
        $users = [
            [
                'name' => 'Pak Bos (Owner)',
                'email' => 'owner@raabiha.com',
                'roles' => ['owner', 'marketing', 'logistics'] // Micromanagement sementara
            ],
            [
                'name' => 'Tim Marketing',
                'email' => 'marketing@raabiha.com',
                'roles' => ['marketing']
            ],
            [
                'name' => 'Tim Finance',
                'email' => 'finance@raabiha.com',
                'roles' => ['finance']
            ],
            [
                'name' => 'Tim Gudang',
                'email' => 'gudang@raabiha.com',
                'roles' => ['logistics']
            ],
            [
                'name' => 'Mbak CS',
                'email' => 'cs@raabiha.com',
                'roles' => ['cs']
            ],
        ];

        foreach ($users as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
            $user->syncRoles($u['roles']);
        }
    }
}
