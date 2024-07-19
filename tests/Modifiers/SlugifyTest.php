<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class SlugifyTest extends TestCase
{
    public static function wordsDontComeEasyToMeProvider(): array
    {
        return [
            ['please-have-some-lemonade', 'Please, have some lemoñade.'],
        ];
    }

    #[Test]
    #[DataProvider('wordsDontComeEasyToMeProvider')]
    public function it_converts_the_string_to_an_url_slug($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->slugify()->fetch();
    }
}
