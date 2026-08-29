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
                Tabs::make('Translations')
                    ->tabs([
                        Tab::make('Українська')
                            ->schema([
                                TextInput::make('name.uk')->label('Name')->required(),
                                TextInput::make('description.uk')->label('Description'),
                            ]),
                        Tab::make('Русский')
                            ->schema([
                                TextInput::make('name.ru')->label('Name'),
                                TextInput::make('description.ru')->label('Description'),
                            ]),
                    ])
                    ->columnSpanFull(),
                TextInput::make('price_for_delivery')
                    ->numeric(),
            ]);
    }
}
