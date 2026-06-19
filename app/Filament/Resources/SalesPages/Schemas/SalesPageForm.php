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
                    ->afterStateUpdated(fn (string $operation, $state, \Filament\Schemas\Components\Utilities\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
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
                        ->action(function (\Filament\Schemas\Components\Utilities\Set $set) {
                            $set('content', [
                                [
                                    'type' => 'section',
                                    'data' => [
                                        'bg_color' => '#1c1c1a',
                                        'padding_y' => 'py-24',
                                        'max_width' => 'max-w-5xl',
                                        'widgets' => [
                                            [
                                                'type' => 'heading',
                                                'data' => [
                                                    'text' => 'Tampil Elegan & Syar\'i Tanpa Menguras Kantong',
                                                    'tag' => 'h1',
                                                    'font_family' => 'font-serif',
                                                    'font_size' => 'text-5xl md:text-6xl',
                                                    'text_align' => 'text-center',
                                                    'text_color' => '#fcf9f5',
                                                ]
                                            ],
                                            [
                                                'type' => 'text',
                                                'data' => [
                                                    'content' => 'Koleksi eksklusif Raabiha didesain khusus untuk muslimah modern yang mengutamakan kenyamanan, kualitas premium, dan tampilan berkelas di setiap momen penting Anda. Spesial hari ini, dapatkan potongan harga eksklusif hingga 40%.',
                                                    'font_size' => 'text-lg md:text-xl',
                                                    'text_align' => 'text-center',
                                                    'text_color' => '#e5e2de',
                                                ]
                                            ],
                                            [
                                                'type' => 'button',
                                                'data' => [
                                                    'text' => 'AMANKAN PROMO SEKARANG',
                                                    'url' => '#checkout',
                                                    'alignment' => 'justify-center',
                                                    'bg_color' => '#fcf9f5',
                                                    'text_color' => '#1c1c1a',
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'type' => 'section',
                                    'data' => [
                                        'bg_color' => '#ffffff',
                                        'padding_y' => 'py-16',
                                        'max_width' => 'max-w-6xl',
                                        'widgets' => [
                                            [
                                                'type' => 'features',
                                                'data' => [
                                                    'items' => [
                                                        ['title' => 'Material Premium Anti-Gerah', 'description' => 'Terbuat dari perpaduan silk dan katun pilihan yang *breathable* (sejuk), ringan, dan jatuh sempurna.', 'icon' => 'heroicon-o-sparkles'],
                                                        ['title' => 'Wudhu & Busui Friendly', 'description' => 'Desain fungsional dengan ritsleting depan tersembunyi dan lengan yang mudah disingkap untuk wudhu.', 'icon' => 'heroicon-o-heart'],
                                                        ['title' => '100% Garansi Kepuasan', 'description' => 'Ukuran tidak pas? Ada cacat? Kami ganti baru tanpa syarat ribet. Belanja tanpa risiko.', 'icon' => 'heroicon-o-shield-check'],
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'type' => 'section',
                                    'data' => [
                                        'bg_color' => '#fcf9f5',
                                        'padding_y' => 'py-16',
                                        'max_width' => 'max-w-6xl',
                                        'widgets' => [
                                            [
                                                'type' => 'heading',
                                                'data' => [
                                                    'text' => 'Apa Kata Pelanggan Kami',
                                                    'tag' => 'h2',
                                                    'font_family' => 'font-serif',
                                                    'font_size' => 'text-4xl',
                                                    'text_align' => 'text-center',
                                                    'text_color' => '#1c1c1a',
                                                ]
                                            ],
                                            [
                                                'type' => 'testimonials',
                                                'data' => [
                                                    'items' => [
                                                        ['name' => 'Aisyah Putri', 'quote' => '"Jujur awalnya ragu karena harganya lumayan terjangkau, tapi pas barangnya sampai... Masya Allah! Jahitannya sekelas butik mahal, bahannya jatuh banget dan nggak nerawang."'],
                                                        ['name' => 'Dian Sastrowardoyo', 'quote' => '"Sudah order ke-3 kalinya. Pengiriman selalu super cepat, dan adminnya ramah banget. Bener-bener dress andalan untuk acara formal maupun santai."'],
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]);
                        }),
                ])->columnSpanFull(),
                \Filament\Forms\Components\Builder::make('content')
                    ->label('Sales Page Builder (Susun Konten Anda)')
                    ->required()
                    ->columnSpanFull()
                    ->blocks([
                        \Filament\Forms\Components\Builder\Block::make('section')
                            ->label('Section Baru (Wadah Konten)')
                            ->icon('heroicon-m-stop')
                            ->schema([
                                \Filament\Schemas\Components\Fieldset::make('Pengaturan Section')
                                    ->schema([
                                        \Filament\Forms\Components\ColorPicker::make('bg_color')
                                            ->label('Background Color')
                                            ->default('#ffffff'),
                                        \Filament\Forms\Components\Select::make('padding_y')
                                            ->label('Padding Vertikal')
                                            ->options([
                                                'py-0' => 'Tidak Ada (0px)',
                                                'py-8' => 'Kecil (32px)',
                                                'py-16' => 'Sedang (64px)',
                                                'py-24' => 'Besar (96px)',
                                                'py-32 md:py-48' => 'Sangat Besar',
                                            ])->default('py-16'),
                                        \Filament\Forms\Components\Select::make('max_width')
                                            ->label('Lebar Konten')
                                            ->options([
                                                'max-w-3xl' => 'Sempit (Maks 768px)',
                                                'max-w-5xl' => 'Sedang (Maks 1024px)',
                                                'max-w-6xl' => 'Lebar (Maks 1152px)',
                                                'max-w-7xl' => 'Sangat Lebar (Maks 1280px)',
                                                'w-full px-6' => 'Penuh (Full Width)',
                                            ])->default('max-w-5xl'),
                                        \Awcodes\Curator\Components\Forms\CuratorPicker::make('bg_image')
                                            ->label('Background Image (Opsional)'),
                                    ])->columns(2),
                                \Filament\Forms\Components\Builder::make('widgets')
                                    ->label('Isi Konten (Widgets)')
                                    ->blocks([
                                        \Filament\Forms\Components\Builder\Block::make('heading')
                                            ->label('Heading / Teks Judul')
                                            ->icon('heroicon-m-h1')
                                            ->schema([
                                                \Filament\Forms\Components\Textarea::make('text')->required(),
                                                \Filament\Forms\Components\Select::make('tag')->options(['h1'=>'H1','h2'=>'H2','h3'=>'H3','h4'=>'H4'])->default('h2'),
                                                \Filament\Forms\Components\Select::make('font_family')->options(['font-sans'=>'Sans (Modern)','font-serif'=>'Serif (Elegan)','font-mono'=>'Monospace'])->default('font-serif'),
                                                \Filament\Forms\Components\Select::make('font_size')->options(['text-2xl'=>'Kecil','text-3xl md:text-4xl'=>'Sedang','text-5xl md:text-6xl'=>'Besar','text-6xl md:text-7xl'=>'Extra Besar'])->default('text-3xl md:text-4xl'),
                                                \Filament\Forms\Components\Select::make('text_align')->options(['text-left'=>'Kiri','text-center'=>'Tengah','text-right'=>'Kanan'])->default('text-center'),
                                                \Filament\Forms\Components\ColorPicker::make('text_color')->default('#1c1c1a'),
                                            ])->columns(3),
                                        \Filament\Forms\Components\Builder\Block::make('text')
                                            ->label('Deskripsi / Teks Panjang')
                                            ->icon('heroicon-m-bars-3-bottom-left')
                                            ->schema([
                                                \Filament\Forms\Components\RichEditor::make('content')->required()->columnSpanFull(),
                                                \Filament\Forms\Components\Select::make('font_size')->options(['text-sm'=>'Kecil','text-base'=>'Normal','text-lg md:text-xl'=>'Besar'])->default('text-base'),
                                                \Filament\Forms\Components\Select::make('text_align')->options(['text-left'=>'Kiri','text-center'=>'Tengah','text-right'=>'Kanan','text-justify'=>'Justify'])->default('text-center'),
                                                \Filament\Forms\Components\ColorPicker::make('text_color')->default('#615e57'),
                                            ])->columns(3),
                                        \Filament\Forms\Components\Builder\Block::make('button')
                                            ->label('Tombol / CTA')
                                            ->icon('heroicon-m-hand-raised')
                                            ->schema([
                                                \Filament\Forms\Components\TextInput::make('text')->required(),
                                                \Filament\Forms\Components\TextInput::make('url')->required(),
                                                \Filament\Forms\Components\Select::make('alignment')->label('Posisi')->options(['justify-start'=>'Kiri','justify-center'=>'Tengah','justify-end'=>'Kanan'])->default('justify-center'),
                                                \Filament\Forms\Components\ColorPicker::make('bg_color')->default('#1c1c1a'),
                                                \Filament\Forms\Components\ColorPicker::make('text_color')->default('#ffffff'),
                                            ])->columns(3),
                                        \Filament\Forms\Components\Builder\Block::make('image')
                                            ->label('Gambar')
                                            ->icon('heroicon-m-photo')
                                            ->schema([
                                                \Awcodes\Curator\Components\Forms\CuratorPicker::make('image')->required()->columnSpanFull(),
                                                \Filament\Forms\Components\Select::make('alignment')->options(['justify-start'=>'Kiri','justify-center'=>'Tengah','justify-end'=>'Kanan'])->default('justify-center'),
                                                \Filament\Forms\Components\TextInput::make('max_width')->label('Lebar Maksimal (misal: 100%, 300px)')->default('100%'),
                                            ])->columns(2),
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
                                                \Filament\Forms\Components\ColorPicker::make('text_color')->label('Warna Teks')->default('#1c1c1a'),
                                            ]),
                                        \Filament\Forms\Components\Builder\Block::make('testimonials')
                                            ->label('Testimonial Grid')
                                            ->icon('heroicon-m-chat-bubble-bottom-center-text')
                                            ->schema([
                                                \Filament\Forms\Components\Repeater::make('items')
                                                    ->schema([
                                                        \Filament\Forms\Components\TextInput::make('name')->required(),
                                                        \Filament\Forms\Components\Textarea::make('quote')->required(),
                                                        \Awcodes\Curator\Components\Forms\CuratorPicker::make('avatar'),
                                                    ])->columns(2),
                                                \Filament\Forms\Components\ColorPicker::make('text_color')->label('Warna Teks')->default('#1c1c1a'),
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
                                                \Filament\Forms\Components\ColorPicker::make('text_color')->label('Warna Teks')->default('#1c1c1a'),
                                            ]),
                                    ])
                                    ->collapsible()
                                    ->columnSpanFull()
                            ])
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
