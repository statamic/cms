<?php

namespace Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class SlugTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    #[DataProvider('slugProvider')]
    public function it_generates_a_slug($string, $separator, $language, $replacements, $expected)
    {
        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->postJson('/cp/slug', [
                'string' => $string,
                'separator' => $separator,
                'language' => $language,
                'replacements' => $replacements,
            ])
            ->assertOk()
            ->assertContent($expected);
    }

    public static function slugProvider()
    {
        return [
            'single word' => ['one', '-', 'en', [], 'one'],
            'multiple words' => ['one two three', '-', 'en', [], 'one-two-three'],
            'apples' => ["Apple's", '-', 'en', [], 'apples'],
            'smart quotes' => ['Statamic’s latest feature: “Duplicator”', '-', 'en', [], 'statamics-latest-feature-duplicator'],
            'hyphens' => ['JSON-LD', '_', 'en', ['-' => '_'], 'json_ld'],
            'hyphens separated by spaces' => ['Block - Hero', '-', 'en', [], 'block-hero'],
            'chinese characters' => ['你好，世界', '-', 'ch', [], 'ni-hao-shi-jie'],
            'german characters' => ['Björn Müller', '-', 'de', [], 'bjoern-mueller'],
            'arabic characters' => ['صباح الخير', '-', 'ar', [], 'sbah-alkhyr'],
            'alternate separator' => ['one two three', '_', 'en', [], 'one_two_three'],
        ];
    }
}
