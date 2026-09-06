<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Назва')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Слаг')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Ціна')
                    ->money()
                    ->sortable(),
                TextColumn::make('discount_price')
                    ->label('Ціна зі знижкою')
                    ->money()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Активний')
                    ->boolean(),
                TextColumn::make('categories.name')
                    ->label('Категорії')
                    ->badge()
                    ->searchable(),
                ImageColumn::make('image')
                    ->label('Зображення'),
                TextColumn::make('created_at')
                    ->label('Дата створення')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Дата оновлення')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
