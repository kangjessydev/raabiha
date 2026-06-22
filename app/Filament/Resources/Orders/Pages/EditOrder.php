<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn () => ! auth()->user()?->hasRole('kasir')),
        ];
    }

    public bool $requestChange = false;

    public function mount($record): void
    {
        parent::mount($record);

        $this->requestChange = request()->query('request_change') === '1' || $this->isRequestChange();
    }

    protected function isRequestChange(): bool
    {
        if (isset($this->requestChange) && $this->requestChange) {
            return true;
        }

        if (request()->query('request_change') === '1') {
            return true;
        }

        $referer = request()->headers->get('referer');
        if ($referer) {
            $query = parse_url($referer, PHP_URL_QUERY);
            if ($query) {
                parse_str($query, $queryParams);
                return isset($queryParams['request_change']) && $queryParams['request_change'] === '1';
            }
        }

        return false;
    }

    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        $action = parent::getSaveFormAction();

        $user = auth()->user();
        if ($user && $user->hasRole('kasir') && $this->isRequestChange()) {
            $hasPendingRequest = $this->getRecord()->orderRequests()
                ->where('type', 'change')
                ->where('status', 'pending')
                ->exists();

            if ($hasPendingRequest) {
                $action->hidden();
            } else {
                $action->label('Ajukan Perubahan');
            }
        }

        return $action;
    }

    public function save(bool $shouldRedirect = true, bool $shouldSendNotification = true): void
    {
        $user = auth()->user();
        if ($user && $user->hasRole('kasir') && $this->isRequestChange()) {
            $hasApprovedRequest = $this->getRecord()->orderRequests()
                ->where('type', 'change')
                ->where('status', 'approved')
                ->exists();

            if (! $hasApprovedRequest) {
                // Cashier is submitting a change request with the proposed changes
                $data = $this->form->getState();

                $changeReason = $data['change_reason'] ?? 'Pengajuan perubahan data pesanan dari halaman edit.';
                unset($data['change_reason']);

                $this->getRecord()->orderRequests()->create([
                    'user_id' => $user->id,
                    'type' => 'change',
                    'reason' => $changeReason,
                    'payload' => $data,
                    'status' => 'pending',
                ]);

                \Filament\Notifications\Notification::make()
                    ->title('Pengajuan perubahan pesanan berhasil dikirim')
                    ->success()
                    ->send();

                $this->redirect($this->getResource()::getUrl('index'));
                return;
            }
        }

        // Default behavior for other roles, or if approved request exists (which saves directly)
        parent::save($shouldRedirect, $shouldSendNotification);
    }
}
