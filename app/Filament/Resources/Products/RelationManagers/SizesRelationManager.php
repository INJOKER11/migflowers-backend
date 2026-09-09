<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SizesRelationManager extends RelationManager
{
    protected static string $relationship = 'sizes';

    protected static ?string $title = 'Розміри';

    protected static ?string $modelLabel = 'розмір';

    protected static ?string $pluralModelLabel = 'розміри';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('size_id')
                    ->label('Розмір')
                    ->relationship('size', 'name')
                    ->required()
                    ->preload(),
                TextInput::make('price_adjustment')
                    ->label('Доплата')
                    ->numeric()
                    ->prefix('₴')
                    ->helperText('Порожньо = без зміни ціни'),
                Toggle::make('is_active')
                    ->label('Активний')
                    ->required()
                    ->default(true),
                Toggle::make('is_default')
                    ->label('За замовчуванням')
                    ->required()
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('size.name')
                    ->label('Розмір'),
                TextColumn::make('price_adjustment')
                    ->label('Доплата')
                    ->money('UAH')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Активний')
                    ->boolean(),
                IconColumn::make('is_default')
                    ->label('За замовчуванням')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
