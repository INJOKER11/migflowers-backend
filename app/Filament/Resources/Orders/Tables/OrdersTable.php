<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Номер замовлення')
                    ->searchable(),
                TextColumn::make('customer_name')
                    ->label('Ім\'я клієнта')
                    ->searchable(),
                TextColumn::make('customer_email')
                    ->label('Електронна пошта клієнта')
                    ->searchable(),
                TextColumn::make('customer_phone')
                    ->label('Телефон клієнта')
                    ->searchable(),
                TextColumn::make('delivery_method')
                    ->label('Спосіб доставки')
                    ->badge()
                    ->sortable(),
                TextColumn::make('delivery_date')
                    ->label('Дата доставки')
                    ->date()
                    ->sortable(),
                TextColumn::make('recipient_name')
                    ->label('Ім\'я отримувача')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->searchable(),
                TextColumn::make('delivery_fee')
                    ->label('Вартість доставки')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('card_fee')
                    ->label('Вартість листівки')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Сума')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Спосіб оплати')
                    ->searchable(),
                TextColumn::make('payment_reference')
                    ->label('Референс платежу')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Дата створення')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Дата оновлення')
                    ->dateTime()
                    ->sortable(),
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
