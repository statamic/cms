<?php

namespace Tests\Data\Taxonomies;

use Statamic\Facades;
use Statamic\Facades\Taxonomy;
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

    /** @test */
    public function if_the_value_is_explicitly_set_to_null_then_it_should_not_fall_back()
    {
        tap(Taxonomy::make('test')->sites(['en', 'fr']))->save();

        $term = (new Term)->taxonomy('test');

        $term->dataForLocale('en', [
            'one' => 'alfa',
            'two' => 'bravo',
            'three' => 'charlie',
        ]);

        $term->dataForLocale('fr', [
            'one' => 'delta',
            'two' => null,
        ]);

        $localized = $term->in('fr');

        $this->assertEquals([
            'one' => 'delta',
            'two' => null,
            'three' => 'charlie',
        ], $localized->values()->all());

        $this->assertEquals('delta', $localized->value('one'));
        $this->assertEquals(null, $localized->value('two'));
        $this->assertEquals('charlie', $localized->value('three'));
    }
}
