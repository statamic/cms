<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class OptionListTest extends TestCase
{
    /** @test */
    public function it_makes_option_lists_with_arrays()
    {
        $this->assertEquals('this', $this->modify(['this']));
        $this->assertEquals('this|that', $this->modify(['this', 'that']));

        $this->assertEquals('this', $this->modify(['this'], '&'));
        $this->assertEquals('this&that', $this->modify(['this', 'that'], '&'));
    }

    /** @test */
    public function it_makes_option_lists_with_collections()
    {
        $this->assertEquals('this', $this->modify(collect(['this'])));
        $this->assertEquals('this|that', $this->modify(collect(['this', 'that'])));

        $this->assertEquals('this', $this->modify(collect(['this']), '&'));
        $this->assertEquals('this&that', $this->modify(collect(['this', 'that']), '&'));
    }

    /** @test */
    public function it_returns_original_value_when_not_iterable()
    {
        $this->assertEquals('foo', $this->modify('foo'));
    }

    public function modify($arr, ...$args)
    {
        return Modify::value($arr)->optionList($args)->fetch();
    }
}
