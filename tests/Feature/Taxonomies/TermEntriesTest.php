<?php

namespace Tests\Feature\Taxonomies;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TermEntriesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_gets_and_counts_entries_for_a_term_across_collections()
    {
        Taxonomy::make('colors')->save();
        Term::make()->taxonomy('colors')->inDefaultLocale()->slug('red')->data([])->save();
        Term::make()->taxonomy('colors')->inDefaultLocale()->slug('black')->data([])->save();
        Term::make()->taxonomy('colors')->inDefaultLocale()->slug('yellow')->data([])->save();

        Collection::make('animals')->taxonomies(['colors'])->save();
        Collection::make('clothes')->taxonomies(['colors'])->save();

        EntryFactory::collection('animals')->slug('panther')->data(['colors' => ['black']])->create();
        EntryFactory::collection('animals')->slug('cheetah')->data(['colors' => ['yellow']])->create();
        EntryFactory::collection('clothes')->slug('red-shirt')->data(['colors' => ['red']])->create();
        EntryFactory::collection('clothes')->slug('black-shirt')->data(['colors' => ['black']])->create();

        $this->assertEquals(1, Term::find('colors::red')->entriesCount());
        $this->assertEquals(2, Term::find('colors::black')->entriesCount());
        $this->assertEquals(1, Term::find('colors::yellow')->entriesCount());

        $this->assertEquals(['red-shirt'], Term::find('colors::red')->entries()->map->slug()->all());
        $this->assertEquals(['panther', 'black-shirt'], Term::find('colors::black')->entries()->map->slug()->all());
        $this->assertEquals(['cheetah'], Term::find('colors::yellow')->entries()->map->slug()->all());

        // and for the base Term class, it should work the same way

        $this->assertEquals(1, Term::find('colors::red')->term()->entriesCount());
        $this->assertEquals(2, Term::find('colors::black')->term()->entriesCount());
        $this->assertEquals(1, Term::find('colors::yellow')->term()->entriesCount());

        $this->assertEquals(['red-shirt'], Term::find('colors::red')->term()->entries()->map->slug()->all());
        $this->assertEquals(['panther', 'black-shirt'], Term::find('colors::black')->term()->entries()->map->slug()->all());
        $this->assertEquals(['cheetah'], Term::find('colors::yellow')->term()->entries()->map->slug()->all());
    }

    #[Test]
    public function it_gets_and_counts_entries_for_a_term_for_a_single_collection()
    {
        Taxonomy::make('colors')->save();
        Term::make()->taxonomy('colors')->inDefaultLocale()->slug('red')->data([])->save();
        Term::make()->taxonomy('colors')->inDefaultLocale()->slug('black')->data([])->save();
        Term::make()->taxonomy('colors')->inDefaultLocale()->slug('yellow')->data([])->save();

        $animals = tap(Collection::make('animals')->taxonomies(['colors']))->save();
        $clothes = tap(Collection::make('clothes')->taxonomies(['colors']))->save();

        EntryFactory::collection('animals')->slug('panther')->data(['colors' => ['black']])->create();
        EntryFactory::collection('animals')->slug('cheetah')->data(['colors' => ['yellow']])->create();
        EntryFactory::collection('clothes')->slug('red-shirt')->data(['colors' => ['red']])->create();
        EntryFactory::collection('clothes')->slug('black-shirt')->data(['colors' => ['black']])->create();

        $this->assertEquals(0, Term::find('colors::red')->collection($animals)->entriesCount());
        $this->assertEquals(1, Term::find('colors::black')->collection($animals)->entriesCount());
        $this->assertEquals(1, Term::find('colors::yellow')->collection($animals)->entriesCount());
        $this->assertEquals(1, Term::find('colors::red')->collection($clothes)->entriesCount());
        $this->assertEquals(1, Term::find('colors::black')->collection($clothes)->entriesCount());
        $this->assertEquals(0, Term::find('colors::yellow')->collection($clothes)->entriesCount());

        $this->assertEquals([], Term::find('colors::red')->collection($animals)->entries()->map->slug()->all());
        $this->assertEquals(['panther'], Term::find('colors::black')->collection($animals)->entries()->map->slug()->all());
        $this->assertEquals(['cheetah'], Term::find('colors::yellow')->collection($animals)->entries()->map->slug()->all());
        $this->assertEquals(['red-shirt'], Term::find('colors::red')->collection($clothes)->entries()->map->slug()->all());
        $this->assertEquals(['black-shirt'], Term::find('colors::black')->collection($clothes)->entries()->map->slug()->all());
        $this->assertEquals([], Term::find('colors::yellow')->collection($clothes)->entries()->map->slug()->all());

        // and for the base Term class, it should work the same way

        $this->assertEquals(0, Term::find('colors::red')->term()->collection($animals)->entriesCount());
        $this->assertEquals(1, Term::find('colors::black')->term()->collection($animals)->entriesCount());
        $this->assertEquals(1, Term::find('colors::yellow')->term()->collection($animals)->entriesCount());
        $this->assertEquals(1, Term::find('colors::red')->term()->collection($clothes)->entriesCount());
        $this->assertEquals(1, Term::find('colors::black')->term()->collection($clothes)->entriesCount());
        $this->assertEquals(0, Term::find('colors::yellow')->term()->collection($clothes)->entriesCount());

        $this->assertEquals([], Term::find('colors::red')->term()->collection($animals)->entries()->map->slug()->all());
        $this->assertEquals(['panther'], Term::find('colors::black')->term()->collection($animals)->entries()->map->slug()->all());
        $this->assertEquals(['cheetah'], Term::find('colors::yellow')->term()->collection($animals)->entries()->map->slug()->all());
        $this->assertEquals(['red-shirt'], Term::find('colors::red')->term()->collection($clothes)->entries()->map->slug()->all());
        $this->assertEquals(['black-shirt'], Term::find('colors::black')->term()->collection($clothes)->entries()->map->slug()->all());
        $this->assertEquals([], Term::find('colors::yellow')->term()->collection($clothes)->entries()->map->slug()->all());
    }

    #[Test]
    public function it_gets_and_counts_entries_for_a_localized_term_across_collections()
    {
        $this->setSites([
            'en' => ['locale' => 'en_US', 'name' => 'English', 'url' => '/'],
            'fr' => ['locale' => 'fr_FR', 'name' => 'French', 'url' => '/fr/'],
        ]);

        Taxonomy::make('colors')->save();
        tap(Term::make()->taxonomy('colors'), function ($term) {
            $term->in('en')->slug('red')->data(['hex' => 'f00'])->save();
            $term->in('fr')->slug('rouge')->save();
        });
        tap(Term::make()->taxonomy('colors'), function ($term) {
            $term->in('en')->slug('black')->data(['hex' => '000'])->save();
            $term->in('fr')->slug('noir')->save();
        });
        tap(Term::make()->taxonomy('colors'), function ($term) {
            $term->in('en')->slug('yellow')->data(['hex' => 'ff0'])->save();
            $term->in('fr')->slug('jaune')->save();
        });

        Collection::make('animals')->taxonomies(['colors'])->save();
        Collection::make('clothes')->taxonomies(['colors'])->save();

        $panther = EntryFactory::collection('animals')->slug('panther')->data(['colors' => ['black']])->create();
        $frPanther = EntryFactory::collection('animals')->locale('fr')->origin($panther->id())->slug('panthere')->data([])->create();

        $cheetah = EntryFactory::collection('animals')->slug('cheetah')->data(['colors' => ['yellow']])->create();
        $frCheetah = EntryFactory::collection('animals')->locale('fr')->origin($cheetah->id())->slug('guepard')->data([])->create();

        $redShirt = EntryFactory::collection('clothes')->slug('red-shirt')->data(['colors' => ['red']])->create();
        $frRedShirt = EntryFactory::collection('clothes')->locale('fr')->origin($redShirt->id())->slug('rouge-shirt')->data([])->create();

        $blackShirt = EntryFactory::collection('clothes')->slug('black-shirt')->data(['colors' => ['black']])->create();
        $frBlackShirt = EntryFactory::collection('clothes')->locale('fr')->origin($blackShirt->id())->slug('noir-shirt')->data([])->create();

        $this->assertEquals(1, Term::find('colors::red')->in('en')->entriesCount());
        $this->assertEquals(['red-shirt'], Term::find('colors::red')->in('en')->entries()->map->slug()->all());
        $this->assertEquals(1, Term::find('colors::red')->in('fr')->entriesCount());
        $this->assertEquals(['rouge-shirt'], Term::find('colors::red')->in('fr')->entries()->map->slug()->all());

        $this->assertEquals(2, Term::find('colors::black')->in('en')->entriesCount());
        $this->assertEquals(['panther', 'black-shirt'], Term::find('colors::black')->in('en')->entries()->map->slug()->all());
        $this->assertEquals(2, Term::find('colors::black')->in('fr')->entriesCount());
        $this->assertEquals(['panthere', 'noir-shirt'], Term::find('colors::black')->in('fr')->entries()->map->slug()->all());

        $this->assertEquals(1, Term::find('colors::yellow')->in('en')->entriesCount());
        $this->assertEquals(['cheetah'], Term::find('colors::yellow')->in('en')->entries()->map->slug()->all());
        $this->assertEquals(1, Term::find('colors::yellow')->in('fr')->entriesCount());
        $this->assertEquals(['guepard'], Term::find('colors::yellow')->in('fr')->entries()->map->slug()->all());

        // and for the base Term class, it should not filter by locale

        $this->assertEquals(2, Term::find('colors::red')->term()->entriesCount());
        $this->assertEquals(['red-shirt', 'rouge-shirt'], Term::find('colors::red')->term()->entries()->map->slug()->all());

        $this->assertEquals(4, Term::find('colors::black')->term()->entriesCount());
        $this->assertEquals(['panther', 'panthere', 'black-shirt', 'noir-shirt'], Term::find('colors::black')->term()->entries()->map->slug()->all());

        $this->assertEquals(2, Term::find('colors::yellow')->term()->entriesCount());
        $this->assertEquals(['cheetah', 'guepard'], Term::find('colors::yellow')->term()->entries()->map->slug()->all());
    }

    #[Test]
    public function it_gets_and_counts_entries_for_a_localized_term_for_a_single_collection()
    {
        $this->setSites([
            'en' => ['locale' => 'en_US', 'name' => 'English', 'url' => '/'],
            'fr' => ['locale' => 'fr_FR', 'name' => 'French', 'url' => '/fr/'],
        ]);

        Taxonomy::make('colors')->save();
        tap(Term::make()->taxonomy('colors'), function ($term) {
            $term->in('en')->slug('red')->data(['hex' => 'f00'])->save();
            $term->in('fr')->slug('rouge')->save();
        });
        tap(Term::make()->taxonomy('colors'), function ($term) {
            $term->in('en')->slug('black')->data(['hex' => '000'])->save();
            $term->in('fr')->slug('noir')->save();
        });
        tap(Term::make()->taxonomy('colors'), function ($term) {
            $term->in('en')->slug('yellow')->data(['hex' => 'ff0'])->save();
            $term->in('fr')->slug('jaune')->save();
        });

        $animals = tap(Collection::make('animals')->taxonomies(['colors']))->save();
        $clothes = tap(Collection::make('clothes')->taxonomies(['colors']))->save();

        $panther = EntryFactory::collection('animals')->slug('panther')->data(['colors' => ['black']])->create();
        $frPanther = EntryFactory::collection('animals')->locale('fr')->origin($panther->id())->slug('panthere')->data([])->create();

        $cheetah = EntryFactory::collection('animals')->slug('cheetah')->data(['colors' => ['yellow']])->create();
        $frCheetah = EntryFactory::collection('animals')->locale('fr')->origin($cheetah->id())->slug('guepard')->data([])->create();

        $redShirt = EntryFactory::collection('clothes')->slug('red-shirt')->data(['colors' => ['red']])->create();
        $frRedShirt = EntryFactory::collection('clothes')->locale('fr')->origin($redShirt->id())->slug('rouge-shirt')->data([])->create();

        $blackShirt = EntryFactory::collection('clothes')->slug('black-shirt')->data(['colors' => ['black']])->create();
        $frBlackShirt = EntryFactory::collection('clothes')->locale('fr')->origin($blackShirt->id())->slug('noir-shirt')->data([])->create();

        $this->assertEquals(0, Term::find('colors::red')->collection($animals)->in('en')->entriesCount());
        $this->assertEquals([], Term::find('colors::red')->collection($animals)->in('en')->entries()->map->slug()->all());
        $this->assertEquals(0, Term::find('colors::red')->collection($animals)->in('fr')->entriesCount());
        $this->assertEquals([], Term::find('colors::red')->collection($animals)->in('fr')->entries()->map->slug()->all());

        $this->assertEquals(1, Term::find('colors::black')->collection($animals)->in('en')->entriesCount());
        $this->assertEquals(['panther'], Term::find('colors::black')->collection($animals)->in('en')->entries()->map->slug()->all());
        $this->assertEquals(1, Term::find('colors::black')->collection($animals)->in('fr')->entriesCount());
        $this->assertEquals(['panthere'], Term::find('colors::black')->collection($animals)->in('fr')->entries()->map->slug()->all());

        $this->assertEquals(1, Term::find('colors::yellow')->collection($animals)->in('en')->entriesCount());
        $this->assertEquals(['cheetah'], Term::find('colors::yellow')->collection($animals)->in('en')->entries()->map->slug()->all());
        $this->assertEquals(1, Term::find('colors::yellow')->collection($animals)->in('fr')->entriesCount());
        $this->assertEquals(['guepard'], Term::find('colors::yellow')->collection($animals)->in('fr')->entries()->map->slug()->all());

        $this->assertEquals(1, Term::find('colors::red')->collection($clothes)->in('en')->entriesCount());
        $this->assertEquals(['red-shirt'], Term::find('colors::red')->collection($clothes)->in('en')->entries()->map->slug()->all());
        $this->assertEquals(1, Term::find('colors::red')->collection($clothes)->in('fr')->entriesCount());
        $this->assertEquals(['rouge-shirt'], Term::find('colors::red')->collection($clothes)->in('fr')->entries()->map->slug()->all());

        $this->assertEquals(1, Term::find('colors::black')->collection($clothes)->in('en')->entriesCount());
        $this->assertEquals(['black-shirt'], Term::find('colors::black')->collection($clothes)->in('en')->entries()->map->slug()->all());
        $this->assertEquals(1, Term::find('colors::black')->collection($clothes)->in('fr')->entriesCount());
        $this->assertEquals(['noir-shirt'], Term::find('colors::black')->collection($clothes)->in('fr')->entries()->map->slug()->all());

        $this->assertEquals(0, Term::find('colors::yellow')->collection($clothes)->in('en')->entriesCount());
        $this->assertEquals([], Term::find('colors::yellow')->collection($clothes)->in('en')->entries()->map->slug()->all());
        $this->assertEquals(0, Term::find('colors::yellow')->collection($clothes)->in('fr')->entriesCount());
        $this->assertEquals([], Term::find('colors::yellow')->collection($clothes)->in('fr')->entries()->map->slug()->all());

        // and for the base Term class, it should not filter by locale

        $this->assertEquals(0, Term::find('colors::red')->collection($animals)->term()->entriesCount());
        $this->assertEquals([], Term::find('colors::red')->collection($animals)->term()->entries()->map->slug()->all());

        $this->assertEquals(2, Term::find('colors::black')->collection($animals)->term()->entriesCount());
        $this->assertEquals(['panther', 'panthere'], Term::find('colors::black')->collection($animals)->term()->entries()->map->slug()->all());

        $this->assertEquals(2, Term::find('colors::yellow')->collection($animals)->term()->entriesCount());
        $this->assertEquals(['cheetah', 'guepard'], Term::find('colors::yellow')->collection($animals)->term()->entries()->map->slug()->all());

        $this->assertEquals(2, Term::find('colors::red')->collection($clothes)->term()->entriesCount());
        $this->assertEquals(['red-shirt', 'rouge-shirt'], Term::find('colors::red')->collection($clothes)->term()->entries()->map->slug()->all());

        $this->assertEquals(2, Term::find('colors::black')->collection($clothes)->term()->entriesCount());
        $this->assertEquals(['black-shirt', 'noir-shirt'], Term::find('colors::black')->collection($clothes)->term()->entries()->map->slug()->all());

        $this->assertEquals(0, Term::find('colors::yellow')->collection($clothes)->term()->entriesCount());
        $this->assertEquals([], Term::find('colors::yellow')->collection($clothes)->term()->entries()->map->slug()->all());
    }

    #[Test]
    public function it_counts_entries_tagged_with_descendant_terms()
    {
        $this->createHierarchicalCategories();

        Collection::make('products')->taxonomies(['categories'])->save();
        EntryFactory::collection('products')->slug('coat')->data(['categories' => ['clothing']])->create();
        EntryFactory::collection('products')->slug('polo')->data(['categories' => ['shirts']])->create();
        EntryFactory::collection('products')->slug('tee')->data(['categories' => ['t-shirts']])->create();
        EntryFactory::collection('products')->slug('apple')->data(['categories' => ['food']])->create();

        // Only one entry is tagged "clothing" directly. Getting 3 means the count
        // walked the whole subtree, and getting "tee" from two levels down means
        // it wasn't just direct children.
        $this->assertEquals(['coat', 'polo', 'tee'], Term::find('categories::clothing')->entries()->map->slug()->all());
        $this->assertEquals(3, Term::find('categories::clothing')->entriesCount());
        $this->assertEquals(3, Term::find('categories::clothing')->term()->entriesCount());

        $this->assertEquals(['polo', 'tee'], Term::find('categories::shirts')->entries()->map->slug()->all());
        $this->assertEquals(2, Term::find('categories::shirts')->entriesCount());
        $this->assertEquals(2, Term::find('categories::shirts')->term()->entriesCount());

        $this->assertEquals(['tee'], Term::find('categories::t-shirts')->entries()->map->slug()->all());
        $this->assertEquals(1, Term::find('categories::t-shirts')->entriesCount());
        $this->assertEquals(1, Term::find('categories::t-shirts')->term()->entriesCount());

        // A sibling branch isn't swept up.
        $this->assertEquals(['apple'], Term::find('categories::food')->entries()->map->slug()->all());
        $this->assertEquals(1, Term::find('categories::food')->entriesCount());
        $this->assertEquals(1, Term::find('categories::food')->term()->entriesCount());
    }

    #[Test]
    public function it_only_counts_entries_tagged_with_a_term_directly_on_a_flat_taxonomy()
    {
        Taxonomy::make('categories')->save();

        foreach (['clothing', 'shirts', 't-shirts', 'food'] as $slug) {
            Term::make($slug)->taxonomy('categories')->data([])->save();
        }

        Collection::make('products')->taxonomies(['categories'])->save();
        EntryFactory::collection('products')->slug('coat')->data(['categories' => ['clothing']])->create();
        EntryFactory::collection('products')->slug('polo')->data(['categories' => ['shirts']])->create();
        EntryFactory::collection('products')->slug('tee')->data(['categories' => ['t-shirts']])->create();
        EntryFactory::collection('products')->slug('apple')->data(['categories' => ['food']])->create();

        $this->assertEquals(1, Term::find('categories::clothing')->entriesCount());
        $this->assertEquals(1, Term::find('categories::shirts')->entriesCount());
        $this->assertEquals(1, Term::find('categories::t-shirts')->entriesCount());
        $this->assertEquals(1, Term::find('categories::food')->entriesCount());
    }

    #[Test]
    public function it_counts_an_entry_tagged_with_both_a_term_and_its_descendant_once()
    {
        $this->createHierarchicalCategories();

        Collection::make('products')->taxonomies(['categories'])->save();
        EntryFactory::collection('products')->slug('tee')->data(['categories' => ['clothing', 't-shirts']])->create();

        $this->assertEquals(['tee'], Term::find('categories::clothing')->entries()->map->slug()->all());
        $this->assertEquals(1, Term::find('categories::clothing')->entriesCount());
    }

    #[Test]
    public function it_counts_descendant_entries_within_a_single_collection()
    {
        $this->createHierarchicalCategories();

        $products = tap(Collection::make('products')->taxonomies(['categories']))->save();
        $articles = tap(Collection::make('articles')->taxonomies(['categories']))->save();

        EntryFactory::collection('products')->slug('tee')->data(['categories' => ['t-shirts']])->create();
        EntryFactory::collection('articles')->slug('tee-review')->data(['categories' => ['t-shirts']])->create();

        $this->assertEquals(2, Term::find('categories::clothing')->entriesCount());
        $this->assertEquals(1, Term::find('categories::clothing')->collection($products)->entriesCount());
        $this->assertEquals(1, Term::find('categories::clothing')->collection($articles)->entriesCount());
    }

    #[Test]
    public function the_augmented_count_only_includes_published_descendant_entries()
    {
        $this->createHierarchicalCategories();

        Collection::make('products')->taxonomies(['categories'])->save();
        EntryFactory::collection('products')->slug('tee')->data(['categories' => ['t-shirts']])->create();
        EntryFactory::collection('products')->slug('polo')->published(false)->data(['categories' => ['shirts']])->create();

        $this->assertEquals(2, Term::find('categories::clothing')->entriesCount());
        $this->assertEquals(1, Term::find('categories::clothing')->toAugmentedArray()['entries_count']->value());
    }

    private function createHierarchicalCategories()
    {
        tap(Taxonomy::make('categories')->structureContents([]))->save();

        foreach (['clothing', 'shirts', 't-shirts', 'food'] as $slug) {
            Term::make($slug)->taxonomy('categories')->data([])->save();
        }

        Taxonomy::findByHandle('categories')->structure()->tree()->tree([
            ['term' => 'clothing', 'children' => [
                ['term' => 'shirts', 'children' => [
                    ['term' => 't-shirts'],
                ]],
            ]],
            ['term' => 'food'],
        ])->save();
    }
}
