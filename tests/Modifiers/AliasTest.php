<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class AliasTest extends TestCase
{
    /** @test */
    public function it_aliases_arrays()
    {
        $arr = ['one', 'two'];

        $this->assertEquals(['as' => ['one', 'two']], $this->modify($arr, 'as'));
    }

    /** @test */
    public function it_aliases_collections()
    {
        $collection = collect(['one', 'two']);

        $this->assertEquals(['as' => $collection], $this->modify($collection, 'as'));
    }

    public function modify($arr, $as)
    {
        return Modify::value($arr)->alias($as)->fetch();
    }
}
