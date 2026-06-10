<?php

namespace App\Observers;

use App\Models\Inquiry;
use App\Models\User;
use Filament\Notifications\Notification;

class InquiryObserver
{
    /**
     * Saat pesan/inquiry baru masuk dari frontend.
     */
    public function created(Inquiry $inquiry): void
    {
        $channel = $inquiry->channel === 'whatsapp' ? 'WhatsApp' : 'Email';
        $admins  = User::role('super_admin')->get();

        if ($admins->isEmpty()) {
            $admins = User::where('id', 2)->get();
        }

        foreach ($admins as $admin) {
            Notification::make()
                ->icon('heroicon-o-envelope')
                ->iconColor('info')
                ->title('Pesan Baru Masuk')
                ->body("Pesan dari {$inquiry->name} via {$channel}: \"{$inquiry->message}\"")
                ->actions([
                    \Filament\Actions\Action::make('view')
                        ->label('Lihat Pesan')
                        ->button()
                        ->url(route('filament.admin.media-files.resources.inquiries.index')),
                ])
                ->sendToDatabase($admin);
        }
    }
}
