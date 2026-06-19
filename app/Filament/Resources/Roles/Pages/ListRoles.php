<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getActions(): array
    {
        return [
            \Filament\Actions\Action::make('generate_roles')
                ->label('Generate Roles & Demo Users')
                ->icon('heroicon-o-users')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Generate Roles & Users')
                ->modalDescription(new \Illuminate\Support\HtmlString('
                    <div class="space-y-3 mt-2 text-sm">
                        <p>Tindakan ini akan mengatur ulang <strong>Spatie Permission</strong> dan membuat akun demo untuk 6 peran berikut:</p>
                        <ul class="list-disc pl-5 space-y-1">
                            <li><strong>Owner:</strong> Akses ke manajemen katalog, transaksi, laporan bisnis, dan pengaturan toko.</li>
                            <li><strong>Marketing:</strong> Akses ke konten CMS (blog, banner), voucher, produk, dan analitik pengunjung.</li>
                            <li><strong>Finance:</strong> Akses penuh ke buku kas, metode pembayaran, dan verifikasi pembayaran pesanan.</li>
                            <li><strong>Warehouse / Logistik:</strong> Akses pembaruan resi pesanan, penyesuaian stok, dan metode pengiriman.</li>
                            <li><strong>Customer Service (CS):</strong> Akses merespons pesan/inquiry, ulasan produk, dan memoderasi komentar.</li>
                            <li><strong>Admin Kasir:</strong> Khusus membuat pesanan (POS manual) dan mencatat transaksi kasir.</li>
                        </ul>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Setiap role akan memiliki 1 akun demo dengan format <em>[nama-role]@raabiha.com</em> (password: <code>password</code>).</p>
                    </div>
                '))
                ->action(function () {
                    \Illuminate\Support\Facades\Artisan::call('app:generate-roles-users');
                    \Filament\Notifications\Notification::make()
                        ->title('Sukses')
                        ->body('Roles dan Demo Users berhasil dibuat!')
                        ->success()
                        ->send();
                })
                ->visible(fn () => auth()->user()->hasRole('super_admin')),
            CreateAction::make(),
        ];
    }
}
