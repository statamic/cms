<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

#[Group('array')]
class ExplodeTest extends TestCase
{
    #[Test]
    public function it_breaks_a_string_into_an_array_of_strings(): void
    {
        $places = 'Scotland, England, Switzerland, Italy';
        $expected = [
            'Scotland',
            'England',
            'Switzerland',
            'Italy',
        ];
        $modified = $this->modify($places, [', ']);
        $this->assertEquals($expected, $modified);

        $places = 'Scotland; England; Switzerland; Italy';
        $modified = $this->modify($places, ['; ']);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value, array $params)
    {
        return Modify::value($value)->explode($params)->fetch();
    }
}
