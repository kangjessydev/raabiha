<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Awcodes\Curator\Components\Forms\RichEditor\AttachCuratorMediaPlugin;
use Awcodes\Curator\Components\Forms\CuratorPicker;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Section::make('Konten Artikel')
                                    ->schema([
                                        Hidden::make('user_id')
                                            ->default(fn() => auth()->id()),
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('title')
                                                    ->label('Judul Artikel')
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn($state, \Filament\Schemas\Components\Utilities\Set $set) => $set('slug', Str::slug($state))),
                                                TextInput::make('slug')
                                                    ->required()
                                                    ->unique(ignoreRecord: true)
                                                    ->live(onBlur: true),
                                            ]),
                                        RichEditor::make('content')
                                            ->label('Isi Artikel')
                                            ->required()
                                            ->columnSpanFull()
                                            ->live(onBlur: true)
                                            ->extraInputAttributes(['style' => 'min-height: 300px;'])
                                            ->toolbarButtons([
                                                'attachFiles',
                                                'attachCuratorMedia',
                                                'blockquote',
                                                'bold',
                                                'bulletList',
                                                'codeBlock',
                                                'h2',
                                                'h3',
                                                'italic',
                                                'link',
                                                'orderedList',
                                                'redo',
                                                'strike',
                                                'underline',
                                                'undo',
                                            ])
                                            ->plugins([
                                                AttachCuratorMediaPlugin::make(),
                                            ]),
                                    ]),
                                Section::make('Search Engine Optimization (SEO)')
                                    ->schema([
                                        TextInput::make('focus_keyword')
                                            ->label('Focus Keyword')
                                            ->maxLength(255)
                                            ->placeholder('Contoh: baju gamis terbaru')
                                            ->live(onBlur: true)
                                            ->columnSpanFull(),
                                        TextInput::make('meta_title')
                                            ->label('Meta Title')
                                            ->maxLength(60)
                                            ->placeholder('Judul SEO (Maks 60 karakter)')
                                            ->live(onBlur: true),
                                        Textarea::make('meta_description')
                                            ->label('Meta Description')
                                            ->maxLength(160)
                                            ->placeholder('Deskripsi singkat untuk pencarian Google (Maks 160 karakter)')
                                            ->live(onBlur: true)
                                            ->columnSpanFull(),
                                        Placeholder::make('seo_indicator')
                                            ->label('Analisis SEO Lengkap')
                                            ->content(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                                $title = $get('title') ?? '';
                                                $slug = $get('slug') ?? '';
                                                $metaTitle = $get('meta_title') ?? '';
                                                $metaDesc = $get('meta_description') ?? '';
                                                $focusKeyword = strtolower(trim($get('focus_keyword') ?? ''));
                                                $contentRaw = $get('content') ?? '';
                                                $contentClean = strip_tags($contentRaw);

                                                // Default meta title to title if empty
                                                $displayTitle = !empty($metaTitle) ? $metaTitle : $title;
                                                if (empty($displayTitle)) {
                                                    $displayTitle = 'Judul Artikel Anda';
                                                }

                                                // Default meta desc if empty
                                                $displayDesc = !empty($metaDesc) ? $metaDesc : Str::limit($contentClean, 150);
                                                if (empty($displayDesc)) {
                                                    $displayDesc = 'Deskripsi artikel Anda akan muncul di sini. Pastikan untuk menulis deskripsi yang menarik agar banyak orang yang mengklik di mesin pencari.';
                                                }

                                                // Calculate word count
                                                $wordCount = str_word_count(preg_replace('/[^\p{L}\p{N}\s]/u', '', $contentClean));

                                                // Snippet Preview HTML
                                                $snippetUrl = url('/blog') . '/' . ($slug ?: 'url-artikel-anda');

                                                $html = '
                                                <div style="background-color: #ffffff; padding: 16px; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 1.5rem; font-family: Arial, sans-serif; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                                                    <div style="font-size: 14px; color: #4d5156; font-weight: bold; margin-bottom: 12px; border-bottom: 1px solid #f3f4f6; padding-bottom: 8px;">Google Search Snippet Preview</div>
                                                    <div style="font-size: 12px; color: #4d5156; display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                                        <div style="width: 28px; height: 28px; background-color: #f1f3f4; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" style="color: #5f6368;"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path><path d="M2 12h20"></path></svg>
                                                        </div>
                                                        <div>
                                                            <span style="display: block; color: #202124; font-size: 14px;">Raabiha Olshop</span>
                                                            <span style="display: block; font-size: 12px;">' . htmlspecialchars($snippetUrl) . '</span>
                                                        </div>
                                                    </div>
                                                    <div style="color: #1a0dab; font-size: 20px; margin-bottom: 4px; font-weight: 400; text-decoration: none; word-wrap: break-word;">
                                                        ' . htmlspecialchars($displayTitle) . '
                                                    </div>
                                                    <div style="color: #4d5156; font-size: 14px; line-height: 1.58; word-wrap: break-word;">
                                                        ' . htmlspecialchars($displayDesc) . '
                                                    </div>
                                                </div>';

                                                $checks = [];

                                                // Word Count Check
                                                if ($wordCount === 0) {
                                                    $checks[] = '<span style="color: #ef4444;">🔴 Konten masih kosong.</span>';
                                                } elseif ($wordCount < 300) {
                                                    $checks[] = '<span style="color: #f59e0b;">🟠 Jumlah kata: <b>' . $wordCount . '</b> (Minimum 300 kata untuk SEO yang baik).</span>';
                                                } else {
                                                    $checks[] = '<span style="color: #10b981;">🟢 Jumlah kata: <b>' . $wordCount . '</b> (Sangat baik).</span>';
                                                }

                                                // Meta Title Check
                                                $titleLen = strlen($metaTitle);
                                                if ($titleLen === 0) {
                                                    $checks[] = '<span style="color: #f59e0b;">🟠 Meta Title kosong (akan otomatis memakai Judul Artikel).</span>';
                                                } elseif ($titleLen < 40) {
                                                    $checks[] = '<span style="color: #f59e0b;">🟠 Meta Title terlalu pendek (rekomendasi 50-60 karakter).</span>';
                                                } elseif ($titleLen > 60) {
                                                    $checks[] = '<span style="color: #ef4444;">🔴 Meta Title terlalu panjang (>60 karakter).</span>';
                                                } else {
                                                    $checks[] = '<span style="color: #10b981;">🟢 Meta Title panjangnya ideal.</span>';
                                                }

                                                // Meta Description Check
                                                $descLen = strlen($metaDesc);
                                                if ($descLen === 0) {
                                                    $checks[] = '<span style="color: #ef4444;">🔴 Meta Description kosong. Sangat disarankan untuk diisi.</span>';
                                                } elseif ($descLen < 120) {
                                                    $checks[] = '<span style="color: #f59e0b;">🟠 Meta Description terlalu pendek (rekomendasi 120-160 karakter).</span>';
                                                } elseif ($descLen > 160) {
                                                    $checks[] = '<span style="color: #ef4444;">🔴 Meta Description terlalu panjang (>160 karakter).</span>';
                                                } else {
                                                    $checks[] = '<span style="color: #10b981;">🟢 Meta Description panjangnya ideal.</span>';
                                                }

                                                // Focus Keyword Check
                                                if (empty($focusKeyword)) {
                                                    $checks[] = '<span style="color: #ef4444;">🔴 Focus Keyword belum diisi.</span>';
                                                } else {
                                                    $hasInTitle = str_contains(strtolower($displayTitle), $focusKeyword);
                                                    $hasInDesc = str_contains(strtolower($displayDesc), $focusKeyword);
                                                    $hasInContent = str_contains(strtolower($contentClean), $focusKeyword);
                                                    $hasInSlug = str_contains(strtolower($slug), str_replace(' ', '-', $focusKeyword));

                                                    // Keyword in URL
                                                    if ($hasInSlug) {
                                                        $checks[] = '<span style="color: #10b981;">🟢 Focus Keyword ditemukan di dalam URL (Slug).</span>';
                                                    } else {
                                                        $checks[] = '<span style="color: #f59e0b;">🟠 Focus Keyword tidak ditemukan di URL.</span>';
                                                    }

                                                    // Keyword in Title
                                                    if ($hasInTitle) {
                                                        $checks[] = '<span style="color: #10b981;">🟢 Focus Keyword ditemukan di Meta Title / Judul.</span>';
                                                    } else {
                                                        $checks[] = '<span style="color: #ef4444;">🔴 Focus Keyword tidak ada di Meta Title.</span>';
                                                    }

                                                    // Keyword in Description
                                                    if ($hasInDesc) {
                                                        $checks[] = '<span style="color: #10b981;">🟢 Focus Keyword ditemukan di Meta Description.</span>';
                                                    } else {
                                                        $checks[] = '<span style="color: #ef4444;">🔴 Focus Keyword tidak ada di Meta Description.</span>';
                                                    }

                                                    // Keyword Density
                                                    if ($hasInContent && $wordCount > 0) {
                                                        $keywordCount = substr_count(strtolower($contentClean), $focusKeyword);
                                                        $keywordWordCount = str_word_count(preg_replace('/[^\p{L}\p{N}\s]/u', '', $focusKeyword)) ?: 1;
                                                        $density = round((($keywordCount * $keywordWordCount) / $wordCount) * 100, 2);

                                                        if ($density < 0.5) {
                                                            $checks[] = '<span style="color: #f59e0b;">🟠 Kepadatan keyword <b>' . $density . '%</b> (Terlalu rendah, disarankan > 0.5%). Kemunculan: ' . $keywordCount . ' kali.</span>';
                                                        } elseif ($density > 2.5) {
                                                            $checks[] = '<span style="color: #ef4444;">🔴 Kepadatan keyword <b>' . $density . '%</b> (Terlalu tinggi, maksimal 2.5% agar tidak spam).</span>';
                                                        } else {
                                                            $checks[] = '<span style="color: #10b981;">🟢 Kepadatan keyword <b>' . $density . '%</b> (Sangat ideal). Kemunculan: ' . $keywordCount . ' kali.</span>';
                                                        }
                                                    } else {
                                                        $checks[] = '<span style="color: #ef4444;">🔴 Focus Keyword tidak ditemukan di dalam Konten.</span>';
                                                    }
                                                }

                                                $html .= '<div style="display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.875rem;">' . implode('', $checks) . '</div>';

                                                return new \Illuminate\Support\HtmlString($html);
                                            })
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columnSpan(2),

                        Grid::make(1)
                            ->schema([
                                Section::make('Publikasi & Klasifikasi')
                                    ->schema([
                                        Toggle::make('is_published')
                                            ->label('Terbitkan')
                                            ->default(true)
                                            ->live()
                                            ->afterStateUpdated(function ($state, \Filament\Schemas\Components\Utilities\Set $set) {
                                                if ($state) {
                                                    $set('published_at', now());
                                                }
                                            }),
                                        DateTimePicker::make('published_at')
                                            ->label('Tanggal Publikasi')
                                            ->default(null),
                                        Select::make('post_category_id')
                                            ->label('Kategori')
                                            ->relationship('category', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->createOptionForm([
                                                TextInput::make('name')
                                                    ->label('Nama Kategori')
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn($state, \Filament\Schemas\Components\Utilities\Set $set) => $set('slug', Str::slug($state))),
                                                TextInput::make('slug')
                                                    ->required()
                                                    ->unique('post_categories', 'slug'),
                                            ]),
                                        Select::make('tags')
                                            ->label('Tag')
                                            ->relationship('tags', 'name')
                                            ->multiple()
                                            ->preload()
                                            ->createOptionForm([
                                                TextInput::make('name')
                                                    ->label('Nama Tag')
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn($state, \Filament\Schemas\Components\Utilities\Set $set) => $set('slug', Str::slug($state))),
                                                TextInput::make('slug')
                                                    ->required()
                                                    ->unique('post_tags', 'slug'),
                                            ]),
                                        CuratorPicker::make('image')
                                            ->label('Gambar Sampul (Media Library)')
                                            ->buttonLabel('Jelajahi Media Library')
                                            ->color('primary')
                                            ->size(\Filament\Support\Enums\Size::Medium),
                                    ]),
                            ])
                            ->columnSpan(1),
                    ])->columnSpanFull(),
            ]);
    }
}
