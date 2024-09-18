<?php

namespace Tests\Modifiers;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Query\Builder;
use Statamic\Facades\Collection as CollectionFacade;
use Statamic\Facades\Entry;
use Statamic\Modifiers\Modify;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('array')]
class LimitTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_limits_arrays(): void
    {
        $arr = ['one', 'two', 'three', 'four', 'five'];

        $this->assertEquals(['one', 'two'], $this->modify($arr, 2));
        $this->assertEquals(['one', 'two', 'three'], $this->modify($arr, 3));
    }

    #[Test]
    public function it_limits_collections(): void
    {
        $collection = collect(['one', 'two', 'three', 'four', 'five']);

        $limited = $this->modify($collection, 2);
        $this->assertInstanceOf(Collection::class, $limited);
        $this->assertEquals(['one', 'two'], $limited->all());

        $limited = $this->modify($collection, 3);
        $this->assertInstanceOf(Collection::class, $limited);
        $this->assertEquals(['one', 'two', 'three'], $limited->all());
    }

    #[Test]
    public function it_limits_builders(): void
    {
        CollectionFacade::make('posts')->save();

        EntryFactory::id('id-1')->collection('posts')->create();
        EntryFactory::id('id-2')->collection('posts')->create();
        EntryFactory::id('id-3')->collection('posts')->create();

        $limited = $this->modify(Entry::query(), 2);
        $this->assertInstanceOf(Builder::class, $limited);
        $this->assertEquals(2, $limited->count());

        $limited = $this->modify(Entry::query(), 3);
        $this->assertInstanceOf(Builder::class, $limited);
        $this->assertEquals(3, $limited->count());
    }

    public function modify($value, $limit)
    {
        return Modify::value($value)->limit($limit)->fetch();
    }
}
