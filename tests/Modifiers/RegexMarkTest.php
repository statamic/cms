<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class RegexMarkTest extends TestCase
{
    /** @test */
    public function it_marks_text()
    {
        $text  = 'Lorem, ipsum dolor sit amet';
        $regex = 'ipsum dolor|amet';

        $expected = 'Lorem, <mark>ipsum dolor</mark> sit <mark>amet</mark>';

        $this->assertEquals($expected, $this->modify($text, $regex));
    }

    /** @test */
    public function it_marks_text_with_class()
    {
        $text  = 'Lorem, ipsum dolor sit amet';
        $regex = 'ipsum dolor';
        $param = 'class:highlight';

        $expected = 'Lorem, <mark class="highlight">ipsum dolor</mark> sit amet';

        $this->assertEquals($expected, $this->modify($text, $regex, $param));
    }

    public function modify($arr, ...$args)
    {
        return Modify::value($arr)->regex_mark($args)->fetch();
    }
}
