<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class SlugifyTest extends TestCase
{
    public function wordsDontComeEasyToMe(): array
    {
        return [
            ['please-have-some-lemonade', 'Please, have some lemoñade.'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider wordsDontComeEasyToMe
     */
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
