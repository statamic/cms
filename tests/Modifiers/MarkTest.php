<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class MarkTest extends TestCase
{
    /** @test */
    public function it_marks_text()
    {
        $text = 'Lorem, ipsum dolor sit amet';
        $words = 'lorem sit';

        $expected = '<mark>Lorem</mark>, ipsum dolor <mark>sit</mark> amet';

        $this->assertEquals($expected, $this->modify($text, $words));
    }

    /** @test */
    public function it_marks_text_with_class()
    {
        $text = 'Lorem, ipsum dolor sit amet';
        $words = 'ipsum';
        $param = 'class:highlight';

        $expected = 'Lorem, <mark class="highlight">ipsum</mark> dolor sit amet';

        $this->assertEquals($expected, $this->modify($text, $words, $param));
    }

    public function modify($arr, ...$args)
    {
        return Modify::value($arr)->mark($args)->fetch();
    }
}
