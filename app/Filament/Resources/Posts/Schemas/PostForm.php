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
                Tabs::make('Translations')
                    ->tabs([
                        Tab::make('Українська')
                            ->schema([
                                TextInput::make('title.uk')->label('Title')->required(),
                                TextInput::make('slug.uk')->label('Slug')->required(),
                                Textarea::make('content.uk')->label('Content')->required()->columnSpanFull(),
                                TextInput::make('subject.uk')->label('Subject')->required(),
                            ]),
                        Tab::make('Русский')
                            ->schema([
                                TextInput::make('title.ru')->label('Title'),
                                TextInput::make('slug.ru')->label('Slug'),
                                Textarea::make('content.ru')->label('Content')->columnSpanFull(),
                                TextInput::make('subject.ru')->label('Subject'),
                            ]),
                    ])
                    ->columnSpanFull(),
                FileUpload::make('image_url')
                    ->image()
                    ->disk('public')
                    ->directory('posts'),
            ]);
    }
}
