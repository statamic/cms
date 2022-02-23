<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

/**
 * @group array
 */
class UniqueTest extends TestCase
{
    /** @test */
    public function it_returns_the_unique_items_in_an_array(): void
    {
        $checklist = [
            'zebra',
            'hippo',
            'hyena',
            'giraffe',
            'zebra',
            'hippo',
            'hippo',
            'hippo',
            'hippo',
        ];
        $expected = [
            'zebra',
            'hippo',
            'hyena',
            'giraffe',
        ];
        $modified = $this->modify($checklist, []);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value, array $params)
    {
        return Modify::value($value)->unique($params)->fetch();
    }
}
