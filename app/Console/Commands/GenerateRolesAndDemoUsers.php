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
                // Cluster Dasbor & Media
                'View:Dashboard', 'View:AnalyticsDashboard', 'View:LaporanBisnis',
                'View:ExportMedia', 'View:ImportMedia', 'View:MainPageSettings',
                'ViewAny:Media', 'View:Media', 'Create:Media', 'Update:Media', 'Delete:Media',
                'ViewAny:SalesPage', 'View:SalesPage', 'Create:SalesPage', 'Update:SalesPage', 'Delete:SalesPage',
                'ViewAny:StaticPage', 'View:StaticPage', 'Create:StaticPage', 'Update:StaticPage', 'Delete:StaticPage',
                
                // Cluster E-Commerce (Full CRUD)
                'ViewAny:TopbarAnnouncement', 'View:TopbarAnnouncement', 'Create:TopbarAnnouncement', 'Update:TopbarAnnouncement', 'Delete:TopbarAnnouncement',
                'ViewAny:PromoBanner', 'View:PromoBanner', 'Create:PromoBanner', 'Update:PromoBanner', 'Delete:PromoBanner',
                'ViewAny:Voucher', 'View:Voucher', 'Create:Voucher', 'Update:Voucher', 'Delete:Voucher',
                'ViewAny:Order', 'View:Order', 'Create:Order', 'Update:Order', 'Delete:Order',
                'ViewAny:Cashflow', 'View:Cashflow', 'Create:Cashflow', 'Update:Cashflow', 'Delete:Cashflow',
                'ViewAny:Product', 'View:Product', 'Create:Product', 'Update:Product', 'Delete:Product',
                'ViewAny:Category', 'View:Category', 'Create:Category', 'Update:Category', 'Delete:Category',
                'ViewAny:Attribute', 'View:Attribute', 'Create:Attribute', 'Update:Attribute', 'Delete:Attribute',
                'ViewAny:ProductReview', 'View:ProductReview', 'Delete:ProductReview',
                'ViewAny:StockLog', 'View:StockLog', 'Create:StockLog', 'Update:StockLog', 'Delete:StockLog',
                'ViewAny:User', 'View:User', 'Create:User', 'Update:User', 'Delete:User', // Daftar Reseller / Pengguna
                'View:ResellerSettings', // Pengaturan Reseller
                'ViewAny:ShippingMethod', 'View:ShippingMethod', 'Create:ShippingMethod', 'Update:ShippingMethod', 'Delete:ShippingMethod',
                'ViewAny:PaymentMethod', 'View:PaymentMethod', 'Create:PaymentMethod', 'Update:PaymentMethod', 'Delete:PaymentMethod',
                
                // Cluster Pengaturan
                'View:GlobalSettings', // Pengaturan Global
            ],
            'marketing' => [
                // Cluster Dasbor, Konten & Media
                'View:Dashboard', 'View:AnalyticsDashboard', 'View:MainPageSettings',
                'ViewAny:SalesPage', 'View:SalesPage', 'Create:SalesPage', 'Update:SalesPage', 'Delete:SalesPage',
                'ViewAny:StaticPage', 'View:StaticPage', 'Create:StaticPage', 'Update:StaticPage', 'Delete:StaticPage',
                'ViewAny:Media', 'View:Media', 'Create:Media', 'Update:Media', 'Delete:Media',
                'ViewAny:Post', 'View:Post', 'Create:Post', 'Update:Post', 'Delete:Post',
                'ViewAny:PostCategory', 'View:PostCategory', 'Create:PostCategory', 'Update:PostCategory', 'Delete:PostCategory',
                'ViewAny:PostTag', 'View:PostTag', 'Create:PostTag', 'Update:PostTag', 'Delete:PostTag',
                
                // Cluster E-Commerce (CRUD Promosi & Katalog)
                'ViewAny:TopbarAnnouncement', 'View:TopbarAnnouncement', 'Create:TopbarAnnouncement', 'Update:TopbarAnnouncement', 'Delete:TopbarAnnouncement',
                'ViewAny:PromoBanner', 'View:PromoBanner', 'Create:PromoBanner', 'Update:PromoBanner', 'Delete:PromoBanner',
                'ViewAny:Voucher', 'View:Voucher', 'Create:Voucher', 'Update:Voucher', 'Delete:Voucher',
                'ViewAny:Product', 'View:Product', 'Create:Product', 'Update:Product', 'Delete:Product',
                'ViewAny:Category', 'View:Category', 'Create:Category', 'Update:Category', 'Delete:Category',
                'ViewAny:Attribute', 'View:Attribute', 'Create:Attribute', 'Update:Attribute', 'Delete:Attribute',
            ],
            'finance' => [
                // Cluster Dasbor & E-Commerce
                'View:Dashboard', 'View:LaporanBisnis', 'View:ExportMedia',
                'ViewAny:Order', 'View:Order', 'Update:Order', // Pesanan: lihat, ubah
                'ViewAny:Cashflow', 'View:Cashflow', 'Create:Cashflow', 'Update:Cashflow', 'Delete:Cashflow', // Buku Kas: CRUD
                'ViewAny:PaymentMethod', 'View:PaymentMethod', 'Update:PaymentMethod', // Metode Pembayaran: lihat, ubah
            ],
            'logistics' => [
                // Cluster Dasbor & E-Commerce
                'View:Dashboard', 'View:ExportMedia', 'View:ImportMedia',
                'ViewAny:Order', 'View:Order', 'Update:Order', // Pesanan: lihat, ubah (resi/status)
                'ViewAny:Product', 'View:Product', // Produk: lihat
                'ViewAny:StockLog', 'View:StockLog', 'Create:StockLog', 'Update:StockLog', // Manajemen Stok: lihat, tambah, ubah
                'ViewAny:ShippingMethod', 'View:ShippingMethod', 'Update:ShippingMethod', // Metode Pengiriman: lihat, ubah
            ],
            'cs' => [
                // Cluster Dasbor, Konten & E-Commerce
                'View:Dashboard',
                'ViewAny:Inquiry', 'View:Inquiry', 'Create:Inquiry', 'Update:Inquiry', 'Delete:Inquiry',
                'ViewAny:PostComment', 'View:PostComment', 'Update:PostComment', 'Delete:PostComment',
                'ViewAny:Order', 'View:Order', 'Create:Order', 'Update:Order', // Pesanan: lihat, buat, ubah
                'ViewAny:Product', 'View:Product', // Produk: lihat
                'ViewAny:ProductReview', 'View:ProductReview', 'Update:ProductReview', 'Delete:ProductReview', // Ulasan: lihat, ubah, hapus
                'ViewAny:User', 'View:User', 'Update:User', // Daftar Reseller/User: lihat, ubah
            ],
            'kasir' => [
                // Cluster Dasbor & E-Commerce
                'View:Dashboard',
                'ViewAny:Order', 'View:Order', 'Create:Order', // Pesanan: lihat, buat (TANPA ubah/hapus)
                'ViewAny:Cashflow', 'View:Cashflow', 'Create:Cashflow', // Buku Kas: lihat, buat (TANPA ubah/hapus)
                'ViewAny:Product', 'View:Product', // Produk: lihat (katalog harga)
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
