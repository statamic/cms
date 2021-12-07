<?php

namespace Tests\Data\Taxonomies;

use Statamic\Facades;
use Statamic\Taxonomies\LocalizedTerm;
use Statamic\Taxonomies\Term;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class LocalizedTermTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_gets_the_entry_count_through_the_repository()
    {
        $term = (new Term)->taxonomy('tags')->slug('foo');
        $localized = new LocalizedTerm($term, 'en');

        $mock = \Mockery::mock(Facades\Term::getFacadeRoot())->makePartial();
        Facades\Term::swap($mock);
        $mock->shouldReceive('entriesCount')->with($localized)->andReturn(7)->once();

        $this->assertEquals(7, $localized->entriesCount());
        $this->assertEquals(7, $localized->entriesCount());
    }
}
