<?php

namespace Tests\Data\Taxonomies;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Stache\Indexes\Terms\Parents;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StructuredTermParentTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function makeTaxonomy()
    {
        $taxonomy = tap(Taxonomy::make('categories')->structureContents([]))->save();

        foreach (['animals', 'cat', 'calico', 'furniture', 'chair'] as $slug) {
            tap(Term::make($slug)->taxonomy('categories')->data(['title' => ucfirst($slug)]))->save();
        }

        $this->saveTree($taxonomy, [
            ['term' => 'animals', 'children' => [
                ['term' => 'cat', 'children' => [
                    ['term' => 'calico'],
                ]],
            ]],
            ['term' => 'furniture', 'children' => [
                ['term' => 'chair'],
            ]],
        ]);

        return $taxonomy;
    }

    private function saveTree($taxonomy, array $tree)
    {
        $taxonomy->structure()->tree()->tree($tree)->save();
    }

    private function parentIndex($taxonomy = 'categories')
    {
        return Stache::store('terms')->store($taxonomy)->index('parent');
    }

    private function childrenOf($parent, $taxonomy = 'categories')
    {
        return Term::query()
            ->where('taxonomy', $taxonomy)
            ->where('parent', $parent)
            ->get()->map->slug()->sort()->values()->all();
    }

    #[Test]
    public function it_queries_terms_by_their_structural_parent()
    {
        $this->makeTaxonomy();

        $this->assertEquals(['cat'], $this->childrenOf('categories::animals'));
        $this->assertEquals(['calico'], $this->childrenOf('categories::cat'));
        $this->assertEquals(['chair'], $this->childrenOf('categories::furniture'));
        $this->assertEquals([], $this->childrenOf('categories::calico'));
    }

    #[Test]
    public function a_term_without_a_parent_is_indexed_as_null()
    {
        $this->makeTaxonomy();

        $this->assertNull($this->parentIndex()->get('en::animals'));
        $this->assertNull($this->parentIndex()->get('en::furniture'));
        $this->assertEquals('categories::animals', $this->parentIndex()->get('en::cat'));
    }

    #[Test]
    public function roots_include_terms_that_only_exist_through_an_association()
    {
        $this->makeTaxonomy();

        Collection::make('blog')->taxonomies(['categories'])->save();
        EntryFactory::collection('blog')->slug('one')->data(['categories' => ['orphan']])->create();

        // Saving the entry pushed the term into every warm index. Rebuild it cold, as
        // after a deploy, so the term only comes from the associations index.
        $this->parentIndex()->update();

        $roots = Term::query()
            ->where('taxonomy', 'categories')
            ->whereNull('parent')
            ->get()->map->slug()->sort()->values()->all();

        $this->assertEquals(['animals', 'furniture', 'orphan'], $roots);
    }

    #[Test]
    public function moving_a_branch_updates_the_parent_index()
    {
        $taxonomy = $this->makeTaxonomy();

        // Build the index before the move so it has something to go stale.
        $this->assertEquals(['cat'], $this->childrenOf('categories::animals'));

        $this->saveTree($taxonomy, [
            ['term' => 'animals'],
            ['term' => 'furniture', 'children' => [
                ['term' => 'chair'],
                ['term' => 'cat', 'children' => [
                    ['term' => 'calico'],
                ]],
            ]],
        ]);

        $this->assertEquals([], $this->childrenOf('categories::animals'));
        $this->assertEquals(['cat', 'chair'], $this->childrenOf('categories::furniture'));
        $this->assertEquals(['calico'], $this->childrenOf('categories::cat'));
    }

    #[Test]
    public function it_updates_the_parent_index_per_term_rather_than_rebuilding_it()
    {
        $taxonomy = $this->makeTaxonomy();

        $store = Stache::store('terms')->store('categories');
        $index = new CountingParentIndex($store, 'parent');
        app('stache.indexes')->put($store->key().'.parent', $index);
        $index->load();
        $index->updates = 0;
        $index->itemUpdates = 0;

        $this->saveTree($taxonomy, [
            ['term' => 'animals'],
            ['term' => 'furniture', 'children' => [
                ['term' => 'chair'],
                ['term' => 'cat', 'children' => [
                    ['term' => 'calico'],
                ]],
            ]],
        ]);

        // Only 'cat' and 'calico' moved. 'calico' kept its parent, but the diff can't
        // tell that apart from a real move, so both get updated.
        $this->assertEquals(0, $index->updates);
        $this->assertEquals(2, $index->itemUpdates);
        $this->assertEquals('categories::furniture', $index->get('en::cat'));
    }

    #[Test]
    public function adding_a_term_to_the_tree_updates_the_parent_index()
    {
        $taxonomy = $this->makeTaxonomy();

        tap(Term::make('dog')->taxonomy('categories')->data(['title' => 'Dog']))->save();

        $this->assertEquals(['cat'], $this->childrenOf('categories::animals'));

        $this->saveTree($taxonomy, [
            ['term' => 'animals', 'children' => [
                ['term' => 'cat', 'children' => [
                    ['term' => 'calico'],
                ]],
                ['term' => 'dog'],
            ]],
            ['term' => 'furniture', 'children' => [
                ['term' => 'chair'],
            ]],
        ]);

        $this->assertEquals(['cat', 'dog'], $this->childrenOf('categories::animals'));
    }

    #[Test]
    public function an_unstructured_taxonomy_queries_a_parent_field_from_its_data()
    {
        Taxonomy::make('tags')->save();

        tap(Term::make('alfa')->taxonomy('tags')->data(['title' => 'Alfa', 'parent' => 'foo']))->save();
        tap(Term::make('bravo')->taxonomy('tags')->data(['title' => 'Bravo']))->save();

        $this->assertEquals(['alfa'], $this->childrenOf('foo', 'tags'));
    }

    #[Test]
    public function only_a_structured_taxonomy_registers_the_parent_index()
    {
        Taxonomy::make('tags')->save();
        $this->makeTaxonomy();

        $this->assertArrayNotHasKey('parent', Stache::store('terms')->store('tags')->indexes(false)->all());
        $this->assertEquals(Parents::class, Stache::store('terms')->store('categories')->indexes(false)->get('parent'));
    }

    #[Test]
    public function a_structured_taxonomy_ignores_a_parent_field_in_its_data()
    {
        $taxonomy = $this->makeTaxonomy();

        tap(Term::find('categories::chair')->term()->set('parent', 'foo'))->save();

        $this->assertEquals([], $this->childrenOf('foo'));
        $this->assertEquals(['chair'], $this->childrenOf('categories::furniture'));
    }

    #[Test]
    public function it_indexes_the_parent_of_each_localization()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr'],
        ]);

        Taxonomy::make('categories')->sites(['en', 'fr'])->structureContents([])->save();

        foreach (['animals', 'cat'] as $slug) {
            tap(Term::make($slug)->taxonomy('categories')->data(['title' => ucfirst($slug)]))->save();
        }

        $this->saveTree(Taxonomy::find('categories'), [
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
            ]],
        ]);

        $this->assertEquals('categories::animals', $this->parentIndex()->get('en::cat'));
        $this->assertEquals('categories::animals', $this->parentIndex()->get('fr::cat'));
        $this->assertEquals(
            ['en', 'fr'],
            Term::query()->where('parent', 'categories::animals')->get()->map->locale()->sort()->values()->all()
        );
    }
}

class CountingParentIndex extends Parents
{
    public $updates = 0;
    public $itemUpdates = 0;

    public function update()
    {
        $this->updates++;

        return parent::update();
    }

    public function updateItem($item)
    {
        $this->itemUpdates++;

        parent::updateItem($item);
    }
}
