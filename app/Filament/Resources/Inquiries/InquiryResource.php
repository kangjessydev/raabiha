<?php

namespace App\Filament\Resources\Inquiries;

use App\Filament\Resources\Inquiries\Pages\ListInquiries;
use App\Filament\Resources\Inquiries\Schemas\InquiryForm;
use App\Filament\Resources\Inquiries\Tables\InquiriesTable;
use App\Models\Inquiry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class InquiryResource extends Resource
{
    protected static ?string $model = Inquiry::class;

    protected static ?string $cluster = \App\Filament\Clusters\MediaFiles::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-envelope';
    protected static \UnitEnum|string|null $navigationGroup = 'Content';
    protected static ?string $navigationLabel = 'Pesan Masuk (Inquiries)';
    protected static ?string $modelLabel = 'Pesan';
    protected static ?string $pluralModelLabel = 'Pesan Masuk';
    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return InquiryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InquiriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInquiries::route('/'),
        ];
    }
}
