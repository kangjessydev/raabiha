<?php

namespace App\Filament\Resources\SalesPages\Schemas;

use Filament\Schemas\Schema;

class SalesPageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('title')
                    ->label('Judul Halaman')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, \Filament\Forms\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                \Filament\Forms\Components\TextInput::make('slug')
                    ->label('URL Slug')
                    ->required()
                    ->unique(\App\Models\SalesPage::class, 'slug', ignoreRecord: true)
                    ->maxLength(255),
                \Filament\Schemas\Components\Actions::make([
                    \Filament\Actions\Action::make('load_template')
                        ->label('Load Template Promo')
                        ->icon('heroicon-m-sparkles')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Gunakan Template Promo?')
                        ->modalDescription('Tindakan ini akan menimpa isi Builder saat ini dengan struktur template bawaan (Hero, Features, Testimonial, FAQ, CTA). Apakah Anda yakin?')
                        ->action(function (\Filament\Forms\Set $set) {
                            $set('content', [
                                [
                                    'type' => 'hero',
                                    'data' => [
                                        'headline' => 'Koleksi Lebaran Eksklusif',
                                        'subheadline' => 'Tampil memukau di hari kemenangan dengan balutan busana premium. Diskon 20% untuk 100 pembeli pertama.',
                                        'button_text' => 'Beli Sekarang',
                                        'button_link' => '#',
                                    ],
                                ],
                                [
                                    'type' => 'features',
                                    'data' => [
                                        'items' => [
                                            ['title' => 'Bahan Premium', 'description' => 'Terbuat dari sutra asli yang dingin dan nyaman di kulit.', 'icon' => 'heroicon-o-sparkles'],
                                            ['title' => 'Jahitan Butik', 'description' => 'Dikerjakan oleh penjahit profesional dengan ketelitian tinggi.', 'icon' => 'heroicon-o-scissors'],
                                            ['title' => 'Garansi Retur', 'description' => 'Tidak pas? Kembalikan kepada kami dalam waktu 7 hari.', 'icon' => 'heroicon-o-arrow-path'],
                                        ]
                                    ],
                                ],
                                [
                                    'type' => 'testimonials',
                                    'data' => [
                                        'items' => [
                                            ['name' => 'Siti Aminah', 'quote' => 'Bajunya sangat nyaman dipakai seharian, potongannya juga pas di badan!'],
                                            ['name' => 'Bunga Citra', 'quote' => 'Pengirimannya cepat dan *packaging*-nya sangat mewah, cocok untuk kado.'],
                                        ]
                                    ],
                                ],
                                [
                                    'type' => 'call_to_action',
                                    'data' => [
                                        'title' => 'Jangan Sampai Kehabisan!',
                                        'description' => 'Stok sangat terbatas. Segera checkout keranjang Anda sekarang juga.',
                                        'button_text' => 'Beli Sekarang (Sisa 12 Pcs)',
                                        'button_link' => '#',
                                    ],
                                ],
                            ]);
                        }),
                ])->columnSpanFull(),
                \Filament\Forms\Components\Builder::make('content')
                    ->label('Sales Page Builder (Susun Konten Anda)')
                    ->required()
                    ->columnSpanFull()
                    ->blocks([
                        \Filament\Forms\Components\Builder\Block::make('hero')
                            ->label('Hero Section')
                            ->icon('heroicon-m-stop')
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('headline')->required(),
                                \Filament\Forms\Components\Textarea::make('subheadline'),
                                \Filament\Forms\Components\TextInput::make('button_text'),
                                \Filament\Forms\Components\TextInput::make('button_link'),
                                \Awcodes\Curator\Components\Forms\CuratorPicker::make('background_image')->label('Background Image')->buttonLabel('Pilih Gambar'),
                            ]),
                        \Filament\Forms\Components\Builder\Block::make('features')
                            ->label('Features Grid')
                            ->icon('heroicon-m-squares-2x2')
                            ->schema([
                                \Filament\Forms\Components\Repeater::make('items')
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('title')->required(),
                                        \Filament\Forms\Components\Textarea::make('description'),
                                        \Filament\Forms\Components\TextInput::make('icon')->label('Icon (Heroicon)'),
                                    ])->columns(3),
                            ]),
                        \Filament\Forms\Components\Builder\Block::make('testimonials')
                            ->label('Testimonial Slider')
                            ->icon('heroicon-m-chat-bubble-bottom-center-text')
                            ->schema([
                                \Filament\Forms\Components\Repeater::make('items')
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('name')->required(),
                                        \Filament\Forms\Components\Textarea::make('quote')->required(),
                                        \Awcodes\Curator\Components\Forms\CuratorPicker::make('avatar'),
                                    ])->columns(2),
                            ]),
                        \Filament\Forms\Components\Builder\Block::make('call_to_action')
                            ->label('Call To Action (CTA)')
                            ->icon('heroicon-m-megaphone')
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('title')->required(),
                                \Filament\Forms\Components\Textarea::make('description'),
                                \Filament\Forms\Components\TextInput::make('button_text')->required(),
                                \Filament\Forms\Components\TextInput::make('button_link')->required(),
                            ]),
                        \Filament\Forms\Components\Builder\Block::make('faq')
                            ->label('FAQ Accordion')
                            ->icon('heroicon-m-question-mark-circle')
                            ->schema([
                                \Filament\Forms\Components\Repeater::make('questions')
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('question')->required(),
                                        \Filament\Forms\Components\Textarea::make('answer')->required(),
                                    ])->columns(1),
                            ]),
                    ])
                    ->collapsible(),
                \Filament\Forms\Components\Toggle::make('is_active')
                    ->label('Aktif & Terpublikasi')
                    ->default(true),
                \Filament\Schemas\Components\Section::make('SEO Settings')
                    ->description('Pengaturan Meta Data untuk Mesin Pencari')
                    ->collapsed()
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->maxLength(255)
                            ->placeholder('Optimal: 50-60 karakter'),
                        \Filament\Forms\Components\Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->rows(3)
                            ->maxLength(160)
                            ->placeholder('Optimal: 150-160 karakter'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
