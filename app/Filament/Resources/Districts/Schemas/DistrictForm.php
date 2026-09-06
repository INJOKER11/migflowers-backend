<?php

namespace App\Filament\Resources\Districts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class DistrictForm
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
                                TextInput::make('description.uk')->label('Опис'),
                            ]),
                        Tab::make('Русский')
                            ->schema([
                                TextInput::make('name.ru')->label('Назва'),
                                TextInput::make('description.ru')->label('Опис'),
                            ]),
                    ])
                    ->columnSpanFull(),
                TextInput::make('price_for_delivery')
                    ->label('Вартість доставки')
                    ->numeric(),
            ]);
    }
}
