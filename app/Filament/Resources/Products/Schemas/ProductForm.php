<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ProductForm
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
                                Textarea::make('description.uk')->label('Description')->columnSpanFull(),
                            ]),
                        Tab::make('Русский')
                            ->schema([
                                TextInput::make('name.ru')->label('Name'),
                                TextInput::make('slug.ru')->label('Slug'),
                                Textarea::make('description.ru')->label('Description')->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('₴'),
                TextInput::make('discount_price')
                    ->numeric()
                    ->prefix('₴'),
                Toggle::make('is_active')
                    ->required(),
                Select::make('category_id')
                    ->relationship(
                        'category',
                        'name',
                        modifyQueryUsing: fn ($query) => $query->orderByRaw("name->>'uk' asc"),
                    )
                    ->required(),
                FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('products')
                    ->maxSize(2048),
            ]);
    }
}
