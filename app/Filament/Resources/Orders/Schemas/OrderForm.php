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
                    ->required(),
                TextInput::make('customer_email')
                    ->email()
                    ->required(),
                TextInput::make('customer_phone')
                    ->tel()
                    ->required(),
                Select::make('delivery_method')
                    ->options([
                        'takeaway' => 'Takeaway',
                        'delivery' => 'Delivery',
                    ])
                    ->required(),
                Select::make('district_id')
                    ->label('District')
                    ->options(fn () => District::query()->pluck('name', 'id'))
                    ->searchable(),
                Textarea::make('delivery_address')
                    ->columnSpanFull(),
                DatePicker::make('delivery_date'),
                TextInput::make('delivery_fee')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('recipient_name'),
                Textarea::make('card_message')
                    ->columnSpanFull(),
                TextInput::make('card_fee')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric(),
                TextInput::make('payment_method')
                    ->required()
                    ->default('online'),
                TextInput::make('stripe_payment_intent_id'),
            ]);
    }
}
