<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Statamic\Support\Arr;
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
        $this->assertEquals($expected, Arr::pluck($modified, 'title'));
    }

    /** @test */
    public function it_has_a_workaround_for_colon_syntax()
    {
        // Before the runtime parser fixed the argument inconsistency, many
        // people probably used the colon syntax. We'll just keep it working.

        $games = [
            ['feeling' => 'love', 'title' => 'Dominion'],
            ['feeling' => 'love', 'title' => 'Netrunner'],
            ['feeling' => 'hate', 'title' => 'Chutes and Ladders'],
        ];
        $expected = ['Dominion', 'Netrunner'];
        $modified = $this->modify($games, ['feeling:love']);
        $this->assertEquals($expected, Arr::pluck($modified, 'title'));
    }

    private function modify($value, array $params)
    {
        return Modify::value($value)->where($params)->fetch();
    }
}
