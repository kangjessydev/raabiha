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
        // Pastikan permissions ter-generate oleh Shield secara non-interaktif
        Artisan::call('shield:generate --all --panel=admin --option=policies_and_permissions');

        $roles = [
            'super_admin' => 'Super Admin',
            'owner' => 'Owner',
            'marketing' => 'Marketing',
            'finance' => 'Finance',
            'logistics' => 'Warehouse / Logistik',
            'cs' => 'Customer Service',
            'kasir' => 'Cashier / Kasir',
        ];

        foreach ($roles as $code => $name) {
            Role::firstOrCreate(['name' => $code], ['guard_name' => 'web']);
        }

        // Definisi Permissions untuk masing-masing role (Super Admin sudah punya semua by Shield)
        $rolePermissions = [
             'owner' => [
                'ViewAny:Order', 'View:Order',
                'ViewAny:Product', 'View:Product',
                'ViewAny:User', 'View:User',
                'ViewAny:Cashflow', 'View:Cashflow',
                'View:LaporanBisnis',
                'View:DashboardStatsOverview', 'View:StatsOverview',
                'View:GoogleAnalytics', 'View:GoogleAnalyticsDashboard',
                'ViewAny:Post', 'View:Post',
                'ViewAny:PostCategory', 'View:PostCategory',
                'ViewAny:PostTag', 'View:PostTag',
                'ViewAny:Comment', 'View:Comment',
                'ViewAny:PostComment', 'View:PostComment',
            ],
            'marketing' => [
                'ViewAny:Product', 'View:Product', 'Create:Product', 'Update:Product',
                'ViewAny:Category', 'View:Category', 'Create:Category', 'Update:Category',
                'ViewAny:Voucher', 'View:Voucher', 'Create:Voucher', 'Update:Voucher',
                'ViewAny:PromoBanner', 'View:PromoBanner', 'Create:PromoBanner', 'Update:PromoBanner',
                'ViewAny:SalesPage', 'View:SalesPage', 'Create:SalesPage', 'Update:SalesPage',
                'ViewAny:Post', 'View:Post', 'Create:Post', 'Update:Post',
                'ViewAny:PostCategory', 'View:PostCategory', 'Create:PostCategory', 'Update:PostCategory',
                'ViewAny:PostTag', 'View:PostTag', 'Create:PostTag', 'Update:PostTag',
                'View:GoogleAnalytics', 'View:GoogleAnalyticsDashboard',
            ],
            'finance' => [
                'ViewAny:Order', 'View:Order', 'Update:Order',
                'ViewAny:Cashflow', 'View:Cashflow', 'Create:Cashflow', 'Update:Cashflow',
                'ViewAny:PaymentMethod', 'View:PaymentMethod',
                'View:LaporanBisnis',
            ],
            'logistics' => [
                'ViewAny:Order', 'View:Order', 'Update:Order',
                'ViewAny:Product', 'View:Product', 'Update:Product', // for stock update
                'ViewAny:ShippingMethod', 'View:ShippingMethod',
            ],
            'cs' => [
                'ViewAny:Inquiry', 'View:Inquiry', 'Update:Inquiry',
                'ViewAny:ProductReview', 'View:ProductReview', 'Update:ProductReview',
                'ViewAny:Product', 'View:Product', // Read-only for product info
                'ViewAny:Order', 'View:Order', // Check order status for customer
                'ViewAny:PostComment', 'View:PostComment', 'Update:PostComment', // Blog comments
            ],
            'kasir' => [
                'ViewAny:Order', 'View:Order', 'Create:Order',
                'ViewAny:Cashflow', 'View:Cashflow', 'Create:Cashflow',
                'ViewAny:Product', 'View:Product',
                'ViewAny:User', 'View:User', 'Create:User',
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
                'roles' => ['owner'] // Micromanagement sementara
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
            [
                'name' => 'Tim Kasir',
                'email' => 'kasir@raabiha.com',
                'roles' => ['kasir']
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
