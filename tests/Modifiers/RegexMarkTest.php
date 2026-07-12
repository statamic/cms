<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class RegexMarkTest extends TestCase
{
    #[Test]
    public function it_marks_with_regex()
    {
        $value = 'Lorem, ipsum dolor sit amet';
        $regex = 'ipsum dolor|amet';

        $expected = 'Lorem, <mark>ipsum dolor</mark> sit <mark>amet</mark>';

        $this->assertEquals($expected, $this->modify($value, $regex));
    }

    public function modify($arr, ...$args)
    {
        return Modify::value($arr)->regex_mark($args)->fetch();
    }
}
