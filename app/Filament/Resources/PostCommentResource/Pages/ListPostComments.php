<?php

namespace App\Filament\Resources\PostCommentResource\Pages;

use App\Filament\Resources\PostCommentResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use App\Models\SiteSetting;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class ListPostComments extends ListRecords
{
    protected static string $resource = PostCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('spam_settings')
                ->label('Pengaturan Kata Kasar')
                ->icon('heroicon-o-shield-check')
                ->color('warning')
                ->modalHeading('Pengaturan Pemfilteran Kata Kasar & Spam')
                ->modalDescription('Komentar yang memuat kata-kata di bawah ini akan otomatis disensor dan ditahan untuk moderasi admin sebelum dapat tayang.')
                ->form([
                    Textarea::make('comment_blacklist_words')
                        ->label('Daftar Kata Kasar (Pisahkan dengan koma)')
                        ->helperText('Contoh: anjing, goblok, bagong, bangsat, babi')
                        ->rows(6)
                        ->default(fn () => SiteSetting::where('key', 'comment_blacklist_words')->value('value') ?: 'anjing, babi, bangsat, keparat, bajingan, kontol, memek, ngentot, jancok, asu, bagong, goblok, tolol, fuck, shit')
                        ->required(),
                ])
                ->action(function (array $data) {
                    SiteSetting::updateOrCreate(
                        ['key' => 'comment_blacklist_words'],
                        ['value' => $data['comment_blacklist_words']]
                    );

                    Notification::make()
                        ->title('Daftar kata kasar berhasil diperbarui!')
                        ->success()
                        ->send();
                })
        ];
    }
}
