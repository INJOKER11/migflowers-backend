<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
                                ...self::seoFields('uk'),
                            ]),
                        Tab::make('Русский')
                            ->schema([
                                TextInput::make('title.ru')->label('Заголовок'),
                                TextInput::make('slug.ru')->label('Слаг'),
                                Textarea::make('content.ru')->label('Зміст')->columnSpanFull(),
                                TextInput::make('subject.ru')->label('Тема'),
                                ...self::seoFields('ru'),
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

    /**
     * Optional overrides for the article page's <title> and meta description
     * in one locale. Left empty, the storefront builds both from the title and
     * the opening of the text. Trimmed, and saved as null when blank, so an
     * accidental space never becomes a title of its own.
     */
    private static function seoFields(string $locale): array
    {
        $clean = fn (?string $state): ?string => filled(trim((string) $state)) ? trim($state) : null;

        return [
            TextInput::make("seo_title.{$locale}")
                ->label('SEO-заголовок')
                ->helperText('Тег <title>. Порожньо — заголовок статті та «| MIG Flowers». Бажано до 60 символів.')
                ->maxLength(255)
                ->dehydrateStateUsing($clean)
                ->columnSpanFull(),
            Textarea::make("meta_description.{$locale}")
                ->label('Meta description')
                ->helperText('Опис у результатах пошуку. Порожньо — початок тексту статті. Бажано до 160 символів.')
                ->rows(3)
                ->maxLength(1000)
                ->dehydrateStateUsing($clean)
                ->columnSpanFull(),
        ];
    }
}
