<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'type',
        'reason',
        'status',
        'actioned_by',
        'notes',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    protected static function booted()
    {
        static::created(function (OrderRequest $orderRequest) {
            // Find recipients with super_admin or owner roles (safely filter to existing ones)
            $existingRoles = \Spatie\Permission\Models\Role::whereIn('name', ['super_admin', 'owner'])->pluck('name')->toArray();
            $recipients = !empty($existingRoles) ? User::role($existingRoles)->get() : collect();
            if ($recipients->isEmpty()) {
                $recipients = User::whereIn('id', [1, 2])->get();
            }

            $typeLabel = $orderRequest->type === 'change' ? 'Perubahan' : 'Pembatalan';
            $cashierName = $orderRequest->user->name ?? 'Kasir';
            $orderNumber = $orderRequest->order->order_number ?? 'Pesanan';

            foreach ($recipients as $recipient) {
                // Database Notification
                \Filament\Notifications\Notification::make()
                    ->icon($orderRequest->type === 'change' ? 'heroicon-o-pencil-square' : 'heroicon-o-no-symbol')
                    ->iconColor($orderRequest->type === 'change' ? 'warning' : 'danger')
                    ->title("Pengajuan {$typeLabel} Pesanan")
                    ->body("Kasir {$cashierName} mengajukan {$typeLabel} untuk pesanan #{$orderNumber}.")
                    ->actions([
                        \Filament\Actions\Action::make('view')
                            ->label('Tinjau')
                            ->button()
                            ->url(route('filament.admin.e-commerce.resources.order-requests.index')),
                    ])
                    ->sendToDatabase($recipient);

                // Email Notification
                try {
                    \Illuminate\Support\Facades\Mail::to($recipient->email)->send(new \App\Mail\StoreMail(
                        subject: "[Persetujuan Kasir] Pengajuan {$typeLabel} Pesanan #{$orderNumber}",
                        view: 'emails.layout',
                        data: [
                            'greeting' => "Halo, {$recipient->name}!",
                            'messageBody' => "Kasir <strong>{$cashierName}</strong> mengajukan permintaan <strong>{$typeLabel}</strong> untuk pesanan <strong>#{$orderNumber}</strong> dengan alasan:<br><br><em>\"{$orderRequest->reason}\"</em>.<br><br>Silakan masuk ke panel admin untuk meninjau dan memberikan keputusan persetujuan.",
                            'actionUrl' => route('filament.admin.e-commerce.resources.order-requests.index'),
                            'actionText' => 'Tinjau Pengajuan'
                        ]
                    ));
                } catch (\Exception $e) {
                    logger()->error("Gagal mengirim email pengajuan kasir ke admin/owner: " . $e->getMessage());
                }
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }
}
