<?php

namespace Tests\Data;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Data\DataCollection;
use Tests\TestCase;

class DataCollectionTest extends TestCase
{
    #[Test]
    public function it_sorts()
    {
        $collection = new DataCollection([
            ['foo' => 'alfa'],
            ['foo' => 'charlie'],
            ['foo' => 'bravo'],
        ]);

        $this->assertEquals(['alfa', 'bravo', 'charlie'], $collection->multisort('foo')->pluck('foo')->all());
    }

    #[Test]
    public function it_sorts_by_first_item_in_arrays()
    {
        $collection = new DataCollection([
            ['id' => 1, 'foos' => ['alfa', 'charlie']],
            ['id' => 2, 'foos' => ['zulu', 'bravo']],
            ['id' => 3, 'foos' => ['delta']],
        ]);

        $this->assertEquals([1, 3, 2], $collection->multisort('foos')->pluck('id')->all());
    }

    #[Test]
    public function sorting_by_unsafe_method_does_not_invoke_it()
    {
        $a = new DataCollectionTestObject('alfa');
        $b = new DataCollectionTestObject('bravo');

        $collection = new DataCollection([$a, $b]);

        $collection->multisort('delete');

        $this->assertFalse($a->deleted);
        $this->assertFalse($b->deleted);
    }
}

class DataCollectionTestObject
{
    public bool $deleted = false;

    public function __construct(public string $title)
    {
    }

    public function delete()
    {
        $this->deleted = true;
    }

    public function get($key)
    {
        return $this->{$key} ?? null;
    }
}
