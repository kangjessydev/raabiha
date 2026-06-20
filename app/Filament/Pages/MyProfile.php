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
    public static function getNavigationSort(): ?int { return 2; }

    protected static ?string $cluster = \App\Filament\Clusters\Settings\SettingsCluster::class;

    protected string $view = 'filament.pages.my-profile';

    public ?\App\Models\User $user = null;
    public ?array $data = [];

    public function mount(): void
    {
        $this->user = auth()->user();
        $this->form->fill($this->user->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                        \Filament\Schemas\Components\Group::make([
                            \Filament\Schemas\Components\View::make('components.avatar-style'),
                            \Awcodes\Curator\Components\Forms\CuratorPicker::make('avatar_url')
                                ->label('')
                                ->buttonLabel('Pilih Foto')
                                ->constrained(true)
                                ->extraAttributes(['class' => 'custom-avatar-picker']),
                        ]),
                \Filament\Schemas\Components\Section::make('Informasi Dasar')
                    ->description('Perbarui informasi nama, email, dan kata sandi Anda.')
                    ->schema([
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
                    ]),
                \Filament\Schemas\Components\Section::make('Alamat & Kontak')
                    ->description('Kelola alamat pengiriman dan nomor telepon Anda.')
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('addresses')
                            ->relationship('addresses')
                            ->label('Daftar Alamat')
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('title')
                                    ->label('Label Alamat (Contoh: Rumah, Kantor)')
                                    ->required(),
                                \Filament\Forms\Components\TextInput::make('recipient_name')
                                    ->label('Nama Penerima')
                                    ->required(),
                                \Filament\Forms\Components\TextInput::make('phone')
                                    ->label('Nomor Telepon')
                                    ->tel()
                                    ->required(),
                                \Filament\Forms\Components\TextInput::make('full_address')
                                    ->label('Alamat Lengkap')
                                    ->required()
                                    ->columnSpanFull(),
                                \Filament\Forms\Components\TextInput::make('province')
                                    ->label('Provinsi')
                                    ->required(),
                                \Filament\Forms\Components\TextInput::make('city')
                                    ->label('Kota/Kabupaten')
                                    ->required(),
                                \Filament\Forms\Components\TextInput::make('district')
                                    ->label('Kecamatan')
                                    ->required(),
                                \Filament\Forms\Components\TextInput::make('postal_code')
                                    ->label('Kode Pos')
                                    ->required(),
                                \Filament\Forms\Components\Toggle::make('is_primary')
                                    ->label('Jadikan Alamat Utama')
                                    ->default(false),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                    ])
            ])
            ->statePath('data')
            ->model($this->user)
            ->columns(1);
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

        $this->user->update($data);
        $this->form->model($this->user)->saveRelationships();

        Notification::make()
            ->title('Profil berhasil diperbarui')
            ->success()
            ->send();
    }
}
