<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class CategoryForm
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
                                TextInput::make('slug.uk')->label('Slug')->required(),
                                TextInput::make('description.uk')->label('Description'),
                            ]),
                        Tab::make('Русский')
                            ->schema([
                                TextInput::make('name.ru')->label('Name'),
                                TextInput::make('slug.ru')->label('Slug'),
                                TextInput::make('description.ru')->label('Description'),
                            ]),
                    ])
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
