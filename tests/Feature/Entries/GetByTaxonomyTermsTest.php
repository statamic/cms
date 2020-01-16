<?php

namespace Tests\Feature\Entries;

use Tests\TestCase;
use Statamic\Facades\Term;
use Statamic\Facades\Entry;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Collection;
use Facades\Tests\Factories\EntryFactory;
use Tests\PreventSavingStacheItemsToDisk;

class GetByTaxonomyTermsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    function it_gets_entries_by_a_single_taxonomy_term()
    {
        Taxonomy::make('tags')->save();
        Collection::make('blog')->taxonomies(['tags'])->save();
        EntryFactory::collection('blog')->slug('one')->data(['tags' => ['rad']])->create();
        EntryFactory::collection('blog')->slug('one')->data(['tags' => ['rad']])->create();
        EntryFactory::collection('blog')->slug('one')->data(['tags' => ['meh']])->create();

        $this->assertEquals(3, Entry::query()->count());
        $this->assertEquals(2, Entry::query()->whereTaxonomy('tags::rad')->count());
    }

    /** @test */
    function it_gets_entries_in_multiple_taxonomy_terms()
    {
        Taxonomy::make('tags')->save();
        Collection::make('blog')->taxonomies(['tags'])->save();
        EntryFactory::collection('blog')->slug('one')->data(['tags' => ['rad']])->create();
        EntryFactory::collection('blog')->slug('one')->data(['tags' => ['awesome']])->create();
        EntryFactory::collection('blog')->slug('one')->data(['tags' => ['rad', 'awesome']])->create();
        EntryFactory::collection('blog')->slug('one')->data(['tags' => ['meh']])->create();

        $this->assertEquals(4, Entry::query()->count());
        $this->assertEquals(1, Entry::query()->whereTaxonomy('tags::rad')->whereTaxonomy('tags::awesome')->count());
        $this->assertEquals(3, Entry::query()->whereTaxonomyIn(['tags::rad', 'tags::meh'])->count());
        $this->assertEquals(1, Entry::query()->whereTaxonomyIn(['tags::rad', 'tags::meh'])->whereTaxonomy('tags::awesome')->count());
    }
}
