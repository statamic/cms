<?php

use Illuminate\Support\Facades\Validator;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UniqueTermValueTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_fails_when_theres_a_duplicate_term_entry_value_in_across_all_taxonomies()
    {
        Taxonomy::make('taxonomy-one')->save();
        Taxonomy::make('taxonomy-two')->save();

        Term::make()->slug('foo')->taxonomy('taxonomy-one')->data(['Foo'])->save();
        Term::make()->slug('bar')->taxonomy('taxonomy-two')->data(['Bar'])->save();

        $this->assertTrue(Validator::make(
            ['slug' => 'foo'],
            ['slug' => 'unique_term_value']
        )->fails());

        $this->assertTrue(Validator::make(
            ['slug' => 'baz'],
            ['slug' => 'unique_term_value']
        )->passes());
    }

    /** @test */
    public function it_fails_when_theres_a_duplicate_term_entry_value_in_a_specific_taxonomy()
    {
        Taxonomy::make('taxonomy-one')->save();
        Taxonomy::make('taxonomy-two')->save();

        Term::make()->slug('foo')->taxonomy('taxonomy-one')->data(['Foo'])->save();
        Term::make()->slug('bar')->taxonomy('taxonomy-two')->data(['Bar'])->save();

        $this->assertTrue(Validator::make(
            ['slug' => 'foo'],
            ['slug' => 'unique_term_value:taxonomy-one']
        )->fails());

        $this->assertTrue(Validator::make(
            ['slug' => 'bar'],
            ['slug' => 'unique_term_value:taxonomy-one']
        )->passes());
    }

    /** @test */
    public function it_passes_duplicate_slug_validation_when_updating_in_a_single_taxonomy()
    {
        Taxonomy::make('taxonomy-one')->save();

        $term = Term::make()->slug('foo')->taxonomy('taxonomy-one')->data(['Foo']);
        $term->save();

        $this->assertTrue(Validator::make(
            ['slug' => 'foo'],
            ['slug' => 'unique_term_value:taxonomy-one,'.$term->id()]
        )->passes());

        $this->assertTrue(Validator::make(
            ['slug' => 'foo'],
            ['slug' => 'unique_term_value:taxonomy-one,456']
        )->fails());
    }

    /** @test */
    public function it_passes_when_theres_a_duplicate_term_value_in_a_different_site()
    {
        \Statamic\Facades\Site::setConfig(['sites' => [
            'site-one' => ['url' => '/'],
            'site-two' => ['url' => '/'],
        ]]);

        Taxonomy::make('taxonomy-one')->save();

        $term = Term::make()->slug('foo')->taxonomy('taxonomy-one')->data(['Foo']);
        $term->save();

        $this->assertTrue(Validator::make(
            ['slug' => 'foo'],
            ['slug' => 'unique_term_value:taxonomy-one,null,site-one']
        )->fails());

        $this->assertTrue(Validator::make(
            ['slug' => 'foo'],
            ['slug' => 'unique_term_value:taxonomy-one,null,site-two']
        )->passes());
    }
}
