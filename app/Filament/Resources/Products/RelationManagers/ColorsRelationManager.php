<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ColorsRelationManager extends RelationManager
{
    protected static string $relationship = 'colors';

    protected static ?string $title = 'Кольори';

    protected static ?string $modelLabel = 'колір';

    protected static ?string $pluralModelLabel = 'кольори';

    public function form(Schema $schema): Schema
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
                FileUpload::make('image')
                    ->label('Зображення')
                    ->image()
                    ->disk('public')
                    ->directory('product-colors')
                    ->maxSize(2048),
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
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('image')
                    ->label('Зображення'),
                TextColumn::make('name')
                    ->label('Назва'),
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
