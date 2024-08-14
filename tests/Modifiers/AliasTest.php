<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

#[Group('array')]
class AliasTest extends TestCase
{
    #[Test]
    public function it_aliases_arrays()
    {
        $arr = ['one', 'two'];

        $this->assertEquals(['as' => ['one', 'two']], $this->modify($arr, 'as'));
    }

    #[Test]
    public function it_aliases_collections()
    {
        $collection = collect(['one', 'two']);

        $this->assertEquals(['as' => $collection], $this->modify($collection, 'as'));
    }

    #[Test]
    public function it_returns_nothing_when_no_array_or_collection_was_passed()
    {
        $noCollection = 'one';

        $this->assertNull($this->modify($noCollection, 'as'));
    }

    public function modify($arr, $as)
    {
        return Modify::value($arr)->alias($as)->fetch();
    }
}
