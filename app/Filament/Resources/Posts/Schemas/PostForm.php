<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Переклади')
                    ->tabs([
                        Tab::make('Українська')
                            ->schema([
                                TextInput::make('title.uk')->label('Заголовок')->required(),
                                TextInput::make('slug.uk')->label('Слаг')->required(),
                                Textarea::make('content.uk')->label('Зміст')->required()->columnSpanFull(),
                                TextInput::make('subject.uk')->label('Тема')->required(),
                            ]),
                        Tab::make('Русский')
                            ->schema([
                                TextInput::make('title.ru')->label('Заголовок'),
                                TextInput::make('slug.ru')->label('Слаг'),
                                Textarea::make('content.ru')->label('Зміст')->columnSpanFull(),
                                TextInput::make('subject.ru')->label('Тема'),
                            ]),
                    ])
                    ->columnSpanFull(),
                FileUpload::make('image_url')
                    ->label('Зображення')
                    ->image()
                    ->disk('public')
                    ->directory('posts'),
            ]);
    }
}
