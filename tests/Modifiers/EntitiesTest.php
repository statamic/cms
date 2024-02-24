<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class EntitiesTest extends TestCase
{
    public static function entityDataProvider(): array
    {
        return [
            ['The &#039;bacon&#039; is &lt;b&gt;crispy&lt;/b&gt;', "The 'bacon' is <b>crispy</b>"],
        ];
    }

    /**
     * @test
     *
     * @dataProvider entityDataProvider
     */
    public function it_encodes_html_entities($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify(string $value)
    {
        return Modify::value($value)->entities()->fetch();
    }
}
