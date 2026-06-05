<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostCommentResource\Pages;
use App\Models\PostComment;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PostCommentResource extends Resource
{
    protected static ?string $model = PostComment::class;
    protected static ?string $cluster = \App\Filament\Clusters\Marketing\MarketingCluster::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';
    
    protected static \UnitEnum|string|null $navigationGroup = 'Blog';
    protected static ?int $navigationSort = 4;
    
    protected static ?string $modelLabel = 'Komentar';
    protected static ?string $pluralModelLabel = 'Komentar';
    protected static ?string $navigationLabel = 'Komentar';

    public static function schema(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('post_id')
                    ->relationship('post', 'title')
                    ->disabled()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('guest_name')
                    ->label('Author')
                    ->disabled(),
                Forms\Components\TextInput::make('guest_email')
                    ->label('Email')
                    ->disabled(),
                Forms\Components\Textarea::make('content')
                    ->label('Comment Content')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('admin_edit_reason')
                    ->label('Alasan Mengubah Komentar (Wajib jika konten diubah)')
                    ->helperText('Teks ini akan ditampilkan kepada pengunjung web.')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->columns([
                Tables\Columns\TextColumn::make('post.title')
                    ->searchable()
                    ->limit(30)
                    ->sortable(),
                Tables\Columns\TextColumn::make('guest_name')
                    ->label('Author')
                    ->state(function (PostComment $record) {
                        if ($record->user_id) {
                            return $record->user->name . ' (Admin)';
                        }
                        return $record->guest_name ?? 'Anonymous';
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('content')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_approved')
            ])
            ->recordActions([
                \Filament\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->hidden(fn (PostComment $record) => $record->is_approved)
                    ->action(fn (PostComment $record) => $record->update(['is_approved' => true])),
                \Filament\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (PostComment $record) => $record->is_approved)
                    ->action(fn (PostComment $record) => $record->update(['is_approved' => false])),
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (!empty($data['admin_edit_reason'])) {
                            $data['is_edited_by_admin'] = true;
                        }
                        return $data;
                    }),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPostComments::route('/'),
        ];
    }
    
    public static function canCreate(): bool
    {
        return false;
    }
}
