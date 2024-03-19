<?php

namespace Feature;

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
    public function it_generates_a_slug($string, $separator, $language, $expected)
    {
        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->postJson('/cp/slug', [
                'string' => $string,
                'separator' => $separator,
                'language' => $language,
            ])
            ->assertOk()
            ->assertContent($expected);
    }

    public static function slugProvider()
    {
        return [
            'single_word' => ['one', '-', 'en', 'one'],
            'multiple_words' => ['one two three', '-', 'en', 'one-two-three'],
            'apples' => ["Apple's", '-', 'en', 'apples'],
            'smart_quotes' => ['Statamic’s latest feature: “Duplicator”', '-', 'en', 'statamics-latest-feature-duplicator'],
            'highens_separated_by_spaces' => ['Block - Hero', '-', 'en', 'block-hero'],
            'chinese_characters' => ['你好，世界', '-', 'ch', 'ni-hao-shi-jie'],
            'german_characters' => ['Björn Müller', '-', 'de', 'bjoern-mueller'],
            'arabic_characters' => ['صباح الخير', '-', 'ar', 'sbah-alkhyr'],
        ];
    }
}
