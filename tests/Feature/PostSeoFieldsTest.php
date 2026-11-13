<?php

namespace Tests\Feature;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class PostSeoFieldsTest extends TestCase
{
    use RefreshDatabase;

    private function form(array $overrides = []): array
    {
        return array_replace_recursive([
            'title' => ['uk' => 'Як зберегти букет', 'ru' => 'Как сохранить букет'],
            'slug' => ['uk' => 'yak-zberehty', 'ru' => 'kak-sohranit'],
            'content' => ['uk' => 'Міняйте воду щодня.', 'ru' => 'Меняйте воду каждый день.'],
            'subject' => ['uk' => 'Догляд', 'ru' => 'Уход'],
            'seo_title' => ['uk' => null, 'ru' => null],
            'meta_description' => ['uk' => null, 'ru' => null],
        ], $overrides);
    }

    public function test_the_form_saves_trimmed_values_per_locale_and_shows_them_again(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreatePost::class)
            ->fillForm($this->form([
                'seo_title' => ['uk' => '  Догляд за букетом — поради  ', 'ru' => ' Уход за букетом '],
                'meta_description' => ['uk' => "  Як продовжити життя букета.\n ", 'ru' => '   '],
            ]))
            ->call('create')
            ->assertHasNoFormErrors();

        $post = Post::sole();
        $this->assertSame('Догляд за букетом — поради', $post->getTranslation('seo_title', 'uk', false));
        $this->assertSame('Уход за букетом', $post->getTranslation('seo_title', 'ru', false));
        $this->assertSame('Як продовжити життя букета.', $post->getTranslation('meta_description', 'uk', false));
        /* Blank is null, not whitespace and not an empty string. */
        $this->assertNull(json_decode($post->getRawOriginal('meta_description'), true)['ru']);

        Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
            ->assertFormSet([
                'seo_title.uk' => 'Догляд за букетом — поради',
                'seo_title.ru' => 'Уход за букетом',
                'meta_description.uk' => 'Як продовжити життя букета.',
            ])
            ->fillForm(['seo_title.uk' => '', 'meta_description.ru' => ' Как продлить жизнь букета. '])
            ->call('save')
            ->assertHasNoFormErrors();

        $post->refresh();
        $this->assertSame('', $post->getTranslation('seo_title', 'uk', false));
        $this->assertSame('Как продлить жизнь букета.', $post->getTranslation('meta_description', 'ru', false));

        Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
            ->assertFormSet([
                'seo_title.ru' => 'Уход за букетом',
                'meta_description.ru' => 'Как продлить жизнь букета.',
            ]);
    }

    public function test_the_api_returns_each_locales_own_values(): void
    {
        Post::create($this->form([
            'seo_title' => ['uk' => 'SEO укр', 'ru' => 'SEO рус'],
            'meta_description' => ['uk' => 'Опис укр', 'ru' => null],
        ]));

        $this->getJson('/api/posts/yak-zberehty?lang=uk')
            ->assertOk()
            ->assertJsonPath('data.seo_title', 'SEO укр')
            ->assertJsonPath('data.meta_description', 'Опис укр');

        /* No fallback to Ukrainian: the storefront falls back to the Russian
           title and text instead. */
        $this->getJson('/api/posts/kak-sohranit?lang=ru')
            ->assertOk()
            ->assertJsonPath('data.seo_title', 'SEO рус')
            ->assertJsonPath('data.meta_description', null);

        $this->getJson('/api/posts?lang=ru')
            ->assertOk()
            ->assertJsonPath('data.0.seo_title', 'SEO рус');
    }

    public function test_a_post_saved_before_the_columns_existed_returns_nulls(): void
    {
        $post = Post::create($this->form());
        /* What the migration leaves on existing rows: the columns themselves NULL. */
        DB::table('posts')->where('id', $post->id)->update(['seo_title' => null, 'meta_description' => null]);

        foreach (['uk' => 'yak-zberehty', 'ru' => 'kak-sohranit'] as $lang => $slug) {
            $this->getJson("/api/posts/{$slug}?lang={$lang}")
                ->assertOk()
                ->assertJsonPath('data.seo_title', null)
                ->assertJsonPath('data.meta_description', null)
                ->assertJsonPath('data.title', $lang === 'uk' ? 'Як зберегти букет' : 'Как сохранить букет');
        }
    }
}
