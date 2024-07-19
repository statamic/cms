<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Statamic\Support\Arr;
use Tests\TestCase;

#[Group('array')]
class WhereTest extends TestCase
{
    #[Test]
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

    #[Test]
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

    #[Test]
    public function it_filters_data_using_operator(): void
    {
        $games = [
            ['feeling' => 'love', 'title' => 'Dominion'],
            ['feeling' => 'love', 'title' => 'Netrunner'],
            ['feeling' => 'hate', 'title' => 'Chutes and Ladders'],
        ];
        $expected = ['Chutes and Ladders'];
        $modified = $this->modify($games, ['feeling', '!=', 'love']);
        $this->assertEquals($expected, Arr::pluck($modified, 'title'));
    }

    private function modify($value, array $params)
    {
        return Modify::value($value)->where($params)->fetch();
    }
}
