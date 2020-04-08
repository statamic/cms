<?php

namespace Tests\Data\Entries;

use Mockery;
use PHPUnit\Framework\TestCase;
use Statamic\Entries\AugmentedEntry;
use Statamic\Entries\Entry;

class AugmentedEntryTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    function it_has_a_parent_method()
    {
        $entry = Mockery::mock(Entry::class);
        $entry->shouldReceive('parent')->andReturn('the parent');

        $augmented = new AugmentedEntry($entry);

        $this->assertEquals('the parent', $augmented->get('parent'));
    }
}
