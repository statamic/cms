<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class GroupByTest extends TestCase
{
    /** @test */
    public function it_groups_an_array()
    {
        $items = [
            ['sport' => 'basketball', 'team' => 'jazz'],
            ['sport' => 'baseball', 'team' => 'yankees'],
            ['sport' => 'basketball', 'team' => 'bulls'],
        ];

        $expected = [
            'basketball' => [
                ['sport' => 'basketball', 'team' => 'jazz'],
                ['sport' => 'basketball', 'team' => 'bulls'],
            ],
            'baseball' => [
                ['sport' => 'baseball', 'team' => 'yankees'],
            ],
            'groups' => [
                ['group' => 'basketball', 'items' => [
                    ['sport' => 'basketball', 'team' => 'jazz'],
                    ['sport' => 'basketball', 'team' => 'bulls'],
                ]],
                ['group' => 'baseball', 'items' => [
                    ['sport' => 'baseball', 'team' => 'yankees'],
                ]],
            ],
        ];

        $this->assertEquals($expected, $this->modify($items, 'sport'));
    }

    public function modify($items, $value)
    {
        return Modify::value($items)->groupBy($value)->fetch();
    }
}
