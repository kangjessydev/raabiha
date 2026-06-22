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
            'kasir' => 'Admin Kasir',
        ];

        foreach ($roles as $code => $name) {
            Role::firstOrCreate(['name' => $code], ['guard_name' => 'web']);
        }

        $this->info('Syncing Role Permissions...');
        $rolePermissions = [
            'owner' => [
                // Cluster Dasbor & Media (Hanya Lihat)
                'View:Dashboard', 'View:AnalyticsDashboard', 'View:LaporanBisnis',
                'View:GoogleAnalytics', 'View:GoogleAnalyticsDashboard',
                'View:MainPageSettings',
                'ViewAny:SalesPage', 'View:SalesPage',
                'ViewAny:StaticPage', 'View:StaticPage',
                
                // Cluster Konten (Hanya Lihat)
                'ViewAny:Post', 'View:Post',
                'ViewAny:PostCategory', 'View:PostCategory',
                'ViewAny:PostTag', 'View:PostTag',
                'ViewAny:Comment', 'View:Comment',
                'ViewAny:PostComment', 'View:PostComment',
                
                // Cluster E-Commerce (Hanya Lihat / Read-Only)
                'ViewAny:Order', 'View:Order',
                'ViewAny:Cashflow', 'View:Cashflow',
                'ViewAny:Product', 'View:Product',
                'ViewAny:Category', 'View:Category',
                'ViewAny:Attribute', 'View:Attribute',
                'ViewAny:ProductReview', 'View:ProductReview',
                'ViewAny:User', 'View:User', // Daftar Reseller / Pengguna
                'View:ResellerSettings', // Pengaturan Reseller
                'ViewAny:ShippingMethod', 'View:ShippingMethod',
                'ViewAny:PaymentMethod', 'View:PaymentMethod',
                'View:GlobalSettings', // Pengaturan Global
            ],
            'marketing' => [
                // Cluster Dasbor, Konten & Media (CRUD Konten & Katalog)
                'View:Dashboard', 'View:AnalyticsDashboard', 'View:MainPageSettings',
                'View:GoogleAnalytics', 'View:GoogleAnalyticsDashboard',
                'ViewAny:SalesPage', 'View:SalesPage', 'Create:SalesPage', 'Update:SalesPage',
                'ViewAny:StaticPage', 'View:StaticPage', 'Create:StaticPage', 'Update:StaticPage',
                'ViewAny:Media', 'View:Media', 'Create:Media', 'Update:Media',
                'ViewAny:Post', 'View:Post', 'Create:Post', 'Update:Post',
                'ViewAny:PostCategory', 'View:PostCategory', 'Create:PostCategory', 'Update:PostCategory',
                'ViewAny:PostTag', 'View:PostTag', 'Create:PostTag', 'Update:PostTag',
                
                // Cluster E-Commerce (CRUD Promosi & Katalog - Tanpa Hapus)
                'ViewAny:TopbarAnnouncement', 'View:TopbarAnnouncement', 'Create:TopbarAnnouncement', 'Update:TopbarAnnouncement',
                'ViewAny:PromoBanner', 'View:PromoBanner', 'Create:PromoBanner', 'Update:PromoBanner',
                'ViewAny:Voucher', 'View:Voucher', 'Create:Voucher', 'Update:Voucher',
                'ViewAny:Product', 'View:Product', 'Create:Product', 'Update:Product',
                'ViewAny:Category', 'View:Category', 'Create:Category', 'Update:Category',
                'ViewAny:Attribute', 'View:Attribute', 'Create:Attribute', 'Update:Attribute',
            ],
            'finance' => [
                // Cluster Dasbor & E-Commerce
                'View:Dashboard', 'View:LaporanBisnis',
                'ViewAny:Order', 'View:Order', 'Update:Order', // Pesanan: lihat, ubah status pembayaran
                'ViewAny:Cashflow', 'View:Cashflow', 'Create:Cashflow', 'Update:Cashflow', 'Delete:Cashflow', 'DeleteAny:Cashflow', // Buku Kas: CRUD
                'ViewAny:PaymentMethod', 'View:PaymentMethod', // Metode Pembayaran: lihat
                'ViewAny:RefundRequest', 'View:RefundRequest', 'Update:RefundRequest',
            ],
            'logistics' => [
                // Cluster Dasbor & E-Commerce
                'View:Dashboard',
                'ViewAny:Order', 'View:Order', 'Update:Order', // Pesanan: lihat, ubah resi/status pengiriman
                'View:Product', 'Update:Product', // Produk: update stok (without ViewAny)
                'ViewAny:ShippingMethod', 'View:ShippingMethod', // Metode Pengiriman: lihat
            ],
            'cs' => [
                // Cluster Dasbor, Konten & E-Commerce
                'View:Dashboard',
                'ViewAny:Inquiry', 'View:Inquiry', 'Update:Inquiry', // Hubungi Kami: lihat, tanggapi
                'ViewAny:PostComment', 'View:PostComment', 'Update:PostComment', // Komentar Blog: lihat, moderasi
                'ViewAny:Order', 'View:Order', // Pesanan: Hanya Lihat
                'ViewAny:Product', 'View:Product', // Produk: Hanya Lihat
                'ViewAny:ProductReview', 'View:ProductReview', 'Update:ProductReview', // Ulasan: lihat, moderasi/balas
            ],
            'kasir' => [
                // Cluster Dasbor & E-Commerce
                'View:Dashboard',
                'ViewAny:Order', 'View:Order', 'Create:Order', 'Update:Order', // Pesanan: lihat, buat (POS manual), edit resi/status
                'ViewAny:Cashflow', 'View:Cashflow', 'Create:Cashflow', // Buku Kas: lihat, buat pencatatan kasir
                'ViewAny:Product', 'View:Product', // Produk: lihat katalog harga
                'ViewAny:User', 'View:User', 'Create:User', // Pelanggan: lihat & tambah saat POS checkout
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
                'roles' => ['owner']
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
                'name' => 'Admin Kasir',
                'email' => 'kasir@raabiha.com',
                'roles' => ['kasir']
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
