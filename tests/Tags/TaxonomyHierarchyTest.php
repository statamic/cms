<?php

namespace Tests\Tags;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Tags\Context;
use Statamic\Tags\Parameters;
use Statamic\Tags\Taxonomy\Terms;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TaxonomyHierarchyTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        tap(Taxonomy::make('categories')->structureContents([]))->save();

        foreach (['animals', 'cat', 'calico', 'tabby', 'furniture'] as $slug) {
            tap(Term::make($slug)->taxonomy('categories')->data(['title' => ucfirst($slug)]))->save();
        }

        Taxonomy::findByHandle('categories')->structure()->tree()->tree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat', 'children' => [
                    ['term' => 'calico'],
                    ['term' => 'tabby'],
                ]],
            ]],
            ['term' => 'furniture'],
        ])->save();
    }

    private function terms($params)
    {
        return (new Terms(Parameters::make($params, new Context)))->get();
    }

    #[Test]
    public function parent_param_gets_direct_children()
    {
        $terms = $this->terms(['from' => 'categories', 'parent' => 'cat']);

        $this->assertEquals(['calico', 'tabby'], $terms->map->slug()->sort()->values()->all());
    }

    #[Test]
    public function parent_param_with_depth_gets_descendants()
    {
        $terms = $this->terms(['from' => 'categories', 'parent' => 'animals', 'depth' => 2]);

        $this->assertEquals(['calico', 'cat', 'tabby'], $terms->map->slug()->sort()->values()->all());
    }

    #[Test]
    public function depth_param_limits_to_top_levels()
    {
        $terms = $this->terms(['from' => 'categories', 'depth' => 1]);

        $this->assertEquals(['animals', 'furniture'], $terms->map->slug()->sort()->values()->all());
    }

    #[Test]
    public function an_unknown_parent_returns_nothing()
    {
        $terms = $this->terms(['from' => 'categories', 'parent' => 'nonexistent']);

        $this->assertCount(0, $terms);
    }

    #[Test]
    public function parent_param_is_ignored_on_flat_taxonomies()
    {
        tap(Taxonomy::make('tags'))->save();
        tap(Term::make('foo')->taxonomy('tags')->data(['title' => 'Foo']))->save();

        $terms = $this->terms(['from' => 'tags', 'parent' => 'anything']);

        $this->assertEquals(['foo'], $terms->map->slug()->all());
    }

    #[Test]
    public function with_descendants_includes_entries_tagged_with_descendant_terms()
    {
        Collection::make('blog')->taxonomies(['categories'])->save();

        EntryFactory::collection('blog')->slug('about-cats')->data(['categories' => ['cat']])->create();
        EntryFactory::collection('blog')->slug('about-calicos')->data(['categories' => ['calico']])->create();
        EntryFactory::collection('blog')->slug('about-couches')->data(['categories' => ['furniture']])->create();

        $without = \Statamic\Facades\Entry::query()->withTaxonomyDescendants(false)->whereTaxonomy('categories::cat')->get();
        $this->assertEquals(['about-cats'], $without->map->slug()->all());

        $with = \Statamic\Facades\Entry::query()->whereTaxonomy('categories::cat')->get();
        $this->assertEquals(['about-calicos', 'about-cats'], $with->map->slug()->sort()->values()->all());
    }

    #[Test]
    public function with_descendants_can_be_disabled()
    {
        Collection::make('blog')->taxonomies(['categories'])->save();

        EntryFactory::collection('blog')->slug('about-cats')->data(['categories' => ['cat']])->create();
        EntryFactory::collection('blog')->slug('about-calicos')->data(['categories' => ['calico']])->create();

        $entries = \Statamic\Facades\Entry::query()->withTaxonomyDescendants(false)->whereTaxonomy('categories::cat')->get();

        $this->assertEquals(['about-cats'], $entries->map->slug()->all());
    }
}
