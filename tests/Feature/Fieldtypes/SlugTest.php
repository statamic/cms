<?php

namespace Tests\Feature\Fieldtypes;

use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SlugTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /**
     * @test
     *
     * @dataProvider slugProvider
     */
    public function it_generates_a_slug($from, $separator, $language, $expected)
    {
        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->post('/cp/fieldtypes/slug', [
                'from' => $from,
                'separator' => $separator,
                'language' => $language,
            ])
            ->assertOk()
            ->assertJson([
                'slug' => $expected,
            ]);
    }

    public function slugProvider()
    {
        return [
            'single_word' => ['one', '-', 'en', 'one'],
            'one-two-three' => ['one two three', '-', 'en', 'one-two-three'],
            'apples' => ["Apple's", '-', 'en', 'apples'],
            'single_smart_quotes' => ['GTA Online’s latest news: “huge map”', '-', 'en', 'gta-onlines-latest-news-huge-map'],
            'highens_separated_by_spaces' => ['Block - Hero', '-', 'en', 'block-hero'],
        ];
    }
}
