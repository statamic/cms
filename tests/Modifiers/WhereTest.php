<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

/**
 * @group array
 */
class WhereTest extends TestCase
{
    /** @test */
    public function it_filters_data_by_a_given_key(): void
    {
        $games = [
            ['feeling' => 'love', 'title' => 'Dominion'],
            ['feeling' => 'love', 'title' => 'Netrunner'],
            ['feeling' => 'hate', 'title' => 'Chutes and Ladders'],
        ];
        $expected = ['Dominion', 'Netrunner'];
        $modified = $this->modify($games, ['feeling', 'love']);
        $this->assertEquals($expected, array_pluck($modified, 'title'));
    }

    private function modify($value, array $params)
    {
        return Modify::value($value)->where($params)->fetch();
    }
}
