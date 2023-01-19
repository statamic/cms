<?php

namespace Tests\Search\Searchables;

use Statamic\Facades\Site;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Search\Searchables\Taxonomies;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TaxonomiesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_finds_terms_from_references()
    {
        Site::setConfig(['sites' => [
            'en' => ['url' => '/'],
            'fr' => ['url' => '/fr/'],
        ]]);

        Taxonomy::make('tags')->sites(['en', 'fr'])->save();
        Term::make('alfa')->taxonomy('tags')->dataForLocale('en', [])->dataForLocale('fr', [])->save();
        Term::make('bravo')->taxonomy('tags')->dataForLocale('en', [])->dataForLocale('fr', [])->save();
        Term::make('charlie')->taxonomy('tags')->dataForLocale('en', [])->dataForLocale('fr', [])->save();

        $found = (new Taxonomies)->find([
            'tags::alfa::en',
            'tags::alfa::fr',
            'tags::bravo::fr',
        ]);

        $this->assertCount(3, $found);
        $this->assertEquals([
            'term::tags::alfa::en',
            'term::tags::alfa::fr',
            // 'term::tags::bravo::en exists, but shouldn't be returned since it's not in the references
            'term::tags::bravo::fr',
        ], $found->map->reference()->all());
    }
}
