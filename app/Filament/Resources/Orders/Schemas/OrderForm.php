<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\District;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('customer_name')
                    ->label('Ім\'я клієнта')
                    ->required(),
                TextInput::make('customer_email')
                    ->label('Електронна пошта клієнта')
                    ->email()
                    ->required(),
                TextInput::make('customer_phone')
                    ->label('Телефон клієнта')
                    ->tel()
                    ->required(),
                Select::make('delivery_method')
                    ->label('Спосіб доставки')
                    ->options([
                        'takeaway' => 'Самовивіз',
                        'delivery' => 'Доставка',
                    ])
                    ->required(),
                Select::make('district_id')
                    ->label('Район')
                    ->options(fn () => District::query()->pluck('name', 'id'))
                    ->searchable(),
                Textarea::make('delivery_address')
                    ->label('Адреса доставки')
                    ->columnSpanFull(),
                DatePicker::make('delivery_date')
                    ->label('Дата доставки'),
                TextInput::make('delivery_fee')
                    ->label('Вартість доставки')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('recipient_name')
                    ->label('Ім\'я отримувача'),
                Textarea::make('card_message')
                    ->label('Текст листівки')
                    ->columnSpanFull(),
                TextInput::make('card_fee')
                    ->label('Вартість листівки')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('status')
                    ->label('Статус')
                    ->required()
                    ->default('pending'),
                TextInput::make('total_amount')
                    ->label('Сума')
                    ->required()
                    ->numeric(),
                TextInput::make('payment_method')
                    ->label('Спосіб оплати')
                    ->required()
                    ->default('online'),
                TextInput::make('payment_reference')
                    ->label('Референс платежу'),
            ]);
    }
}
