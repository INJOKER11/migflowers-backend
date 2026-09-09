<?php

namespace App\Filament\Resources\Sizes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class SizeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Переклади')
                    ->tabs([
                        Tab::make('Українська')
                            ->schema([
                                TextInput::make('name.uk')->label('Назва')->required(),
                            ]),
                        Tab::make('Русский')
                            ->schema([
                                TextInput::make('name.ru')->label('Назва'),
                            ]),
                    ])
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->label('Порядок сортування')
                    ->numeric()
                    ->integer()
                    ->default(0)
                    ->required(),
            ]);
    }
}
