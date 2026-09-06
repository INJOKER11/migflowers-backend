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
                Tabs::make('Переклади')
                    ->tabs([
                        Tab::make('Українська')
                            ->schema([
                                TextInput::make('name.uk')->label('Назва')->required(),
                                TextInput::make('slug.uk')->label('Слаг')->required(),
                                TextInput::make('description.uk')->label('Опис'),
                            ]),
                        Tab::make('Русский')
                            ->schema([
                                TextInput::make('name.ru')->label('Назва'),
                                TextInput::make('slug.ru')->label('Слаг'),
                                TextInput::make('description.ru')->label('Опис'),
                            ]),
                    ])
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Активний')
                    ->required(),
            ]);
    }
}
