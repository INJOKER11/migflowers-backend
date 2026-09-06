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
use Illuminate\Support\Facades\DB;

class ProductForm
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
                                Textarea::make('description.uk')->label('Опис')->columnSpanFull(),
                            ]),
                        Tab::make('Русский')
                            ->schema([
                                TextInput::make('name.ru')->label('Назва'),
                                TextInput::make('slug.ru')->label('Слаг'),
                                Textarea::make('description.ru')->label('Опис')->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label('Ціна')
                    ->required()
                    ->numeric()
                    ->prefix('₴'),
                TextInput::make('discount_price')
                    ->label('Ціна зі знижкою')
                    ->numeric()
                    ->prefix('₴'),
                Toggle::make('is_active')
                    ->label('Активний')
                    ->required(),
                Select::make('categories')
                    ->label('Категорії')
                    ->relationship(
                        'categories',
                        'name',
                        modifyQueryUsing: fn ($query) => $query
                            ->select('categories.*', DB::raw("name->>'uk' as name_uk"))
                            ->orderBy('name_uk'),
                    )
                    ->multiple()
                    ->preload()
                    ->required(),
                FileUpload::make('image')
                    ->label('Зображення')
                    ->image()
                    ->disk('public')
                    ->directory('products')
                    ->maxSize(2048),
            ]);
    }
}
