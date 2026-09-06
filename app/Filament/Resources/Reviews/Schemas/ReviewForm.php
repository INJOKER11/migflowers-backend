<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Ім\'я')
                    ->required(),
                TextInput::make('product_name')
                    ->label('Назва товару'),
                Textarea::make('comment')
                    ->label('Коментар')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('rating')
                    ->label('Оцінка')
                    ->required()
                    ->numeric()
                    ->maxValue(5),
            ]);
    }
}
