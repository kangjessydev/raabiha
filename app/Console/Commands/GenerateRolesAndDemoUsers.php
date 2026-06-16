<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

class GenerateRolesAndDemoUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-roles-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate default roles and demo users for the ecommerce system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating Filament Shield permissions...');
        Artisan::call('shield:generate', [
            '--all' => true,
            '--option' => 'policies_and_permissions',
            '--panel' => 'admin',
        ]);

        $this->info('Setting up Roles...');
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

        $this->info('Syncing Role Permissions...');
        $rolePermissions = [
            'owner' => [
                'ViewAny:Order', 'View:Order',
                'ViewAny:Product', 'View:Product',
                'ViewAny:User', 'View:User',
                'ViewAny:Cashflow', 'View:Cashflow',
                'View:OrderStatsWidget', 'View:RevenueChart',
            ],
            'marketing' => [
                'ViewAny:Product', 'View:Product', 'Create:Product', 'Update:Product',
                'ViewAny:Category', 'View:Category', 'Create:Category', 'Update:Category',
                'ViewAny:Voucher', 'View:Voucher', 'Create:Voucher', 'Update:Voucher',
                'ViewAny:PromoBanner', 'View:PromoBanner', 'Create:PromoBanner', 'Update:PromoBanner',
                'ViewAny:SalesPage', 'View:SalesPage', 'Create:SalesPage', 'Update:SalesPage',
            ],
            'finance' => [
                'ViewAny:Order', 'View:Order', 'Update:Order',
                'ViewAny:Cashflow', 'View:Cashflow', 'Create:Cashflow', 'Update:Cashflow',
                'ViewAny:PaymentMethod', 'View:PaymentMethod',
                'View:RevenueChart',
            ],
            'logistics' => [
                'ViewAny:Order', 'View:Order', 'Update:Order',
                'ViewAny:Product', 'View:Product', 'Update:Product',
                'ViewAny:StockLog', 'View:StockLog', 'Create:StockLog',
                'ViewAny:ShippingMethod', 'View:ShippingMethod',
            ],
            'cs' => [
                'ViewAny:Inquiry', 'View:Inquiry', 'Update:Inquiry',
                'ViewAny:ProductReview', 'View:ProductReview', 'Update:ProductReview',
                'ViewAny:Product', 'View:Product',
                'ViewAny:Order', 'View:Order',
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $validPerms = Permission::whereIn('name', $perms)->pluck('name')->toArray();
                $role->syncPermissions($validPerms);
            }
        }

        $this->info('Setting up Demo Users...');
        $users = [
            [
                'name' => 'Pak Bos (Owner)',
                'email' => 'owner@raabiha.com',
                'roles' => ['owner', 'marketing', 'logistics']
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
                'name' => 'Super Admin Baru',
                'email' => 'superadmin@raabiha.com',
                'roles' => ['super_admin']
            ]
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

        $this->info('Generation Complete! You can now login with superadmin@raabiha.com / password');
    }
}
