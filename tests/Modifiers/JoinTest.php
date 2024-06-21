<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

#[Group('array')]
class JoinTest extends TestCase
{
    #[Test]
    public function it_joins_values_of_an_array_using_a_comma_by_default()
    {
        $joined = $this->modify(['foo', 'bar']);

        $this->assertEquals('foo, bar', $joined);
    }

    #[Test]
    public function it_joins_values_of_an_array_using_a_custom_delimiter()
    {
        $joined = $this->modify(['foo', 'bar'], '+');

        $this->assertEquals('foo+bar', $joined);
    }

    #[Test]
    public function it_returns_empty_string_when_value_is_null()
    {
        $joined = $this->modify(null, ' ');

        $this->assertEquals('', $joined);
    }

    public function modify($arr, $delimiter = null)
    {
        return Modify::value($arr)->join($delimiter)->fetch();
    }
}
