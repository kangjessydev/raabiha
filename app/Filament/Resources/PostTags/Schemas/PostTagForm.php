<?php

namespace App\Filament\Resources\PostTags\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PostTagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
            ]);
    }
}
