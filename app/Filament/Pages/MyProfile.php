<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Support\Facades\Hash;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class MyProfile extends Page implements HasForms
{
    use InteractsWithForms;

    public static function getNavigationIcon(): ?string { return 'heroicon-o-user-circle'; }
    public static function getNavigationGroup(): string|\BackedEnum|null { return 'Manajemen Pengguna'; }
    public static function getNavigationLabel(): string { return 'Profil Saya'; }
    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable { return 'Profil Saya'; }
    public static function getNavigationSort(): ?int { return 1; }

    protected static ?string $cluster = \App\Filament\Clusters\Settings\SettingsCluster::class;

    protected string $view = 'filament.pages.my-profile';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'avatar_url' => auth()->user()->avatar_url,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Informasi Akun')
                    ->description('Perbarui informasi profil dan kata sandi Anda.')
                    ->schema([
                        \Awcodes\Curator\Components\Forms\CuratorPicker::make('avatar_url')
                            ->label('Foto Profil')
                            ->buttonLabel('Pilih Foto Profil'),
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(table: 'users', ignorable: auth()->user()),
                        TextInput::make('password')
                            ->label('Password Baru')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->helperText('Kosongkan jika tidak ingin mengubah password.')
                            ->revealable(),
                    ])->columns(1)->maxWidth('md'),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Perubahan')
                ->submit('save')
                ->color('primary'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $user = auth()->user();
        $user->update($data);

        Notification::make()
            ->title('Profil berhasil diperbarui')
            ->success()
            ->send();
    }
}
