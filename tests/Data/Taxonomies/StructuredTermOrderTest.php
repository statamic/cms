<?php

namespace Tests\Data\Taxonomies;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Stache\Indexes\Terms\Value;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StructuredTermOrderTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function makeTaxonomy()
    {
        $taxonomy = tap(Taxonomy::make('categories')->structureContents([]))->save();

        foreach (['animals', 'cat', 'calico', 'furniture', 'chair'] as $slug) {
            tap(Term::make($slug)->taxonomy('categories')->data(['title' => ucfirst($slug)]))->save();
        }

        return $taxonomy;
    }

    private function saveTree($taxonomy, array $tree)
    {
        $taxonomy->structure()->tree()->tree($tree)->save();
    }

    private function nestedTree()
    {
        return [
            ['term' => 'animals', 'children' => [
                ['term' => 'cat', 'children' => [
                    ['term' => 'calico'],
                ]],
            ]],
            ['term' => 'furniture'],
            ['term' => 'chair'],
        ];
    }

    private function orderIndex()
    {
        return Stache::store('terms')->store('categories')->index('order');
    }

    private function installOrderIndexSpy()
    {
        $store = Stache::store('terms')->store('categories');

        $index = new CountingOrderIndex($store, 'order');

        app('stache.indexes')->put($store->key().'.order', $index);

        return tap($index->load())->resetCounts();
    }

    private function assertOrders(array $expected)
    {
        foreach ($expected as $slug => $order) {
            $this->assertEquals($order, Term::find("categories::{$slug}")->order(), "Order of [{$slug}]");
            $this->assertEquals($order, $this->orderIndex()->get("en::{$slug}"), "Indexed order of [{$slug}]");
        }
    }

    #[Test]
    public function persisting_a_tree_indexes_the_order_of_the_terms_added_to_it()
    {
        $taxonomy = $this->makeTaxonomy();

        // Creating the terms indexed them in creation order, so the tree
        // below is the only thing that can produce these orders.
        $this->saveTree($taxonomy, [
            ['term' => 'chair'],
            ['term' => 'furniture'],
            ['term' => 'animals', 'children' => [
                ['term' => 'cat', 'children' => [
                    ['term' => 'calico'],
                ]],
            ]],
        ]);

        $this->assertOrders(['chair' => 1, 'furniture' => 2, 'animals' => 3, 'cat' => 4, 'calico' => 5]);
    }

    #[Test]
    public function moving_a_branch_updates_the_order_of_its_descendants()
    {
        $taxonomy = $this->makeTaxonomy();

        $this->saveTree($taxonomy, $this->nestedTree());

        $this->assertOrders(['animals' => 1, 'cat' => 2, 'calico' => 3, 'furniture' => 4, 'chair' => 5]);

        $this->saveTree($taxonomy, [
            ['term' => 'furniture'],
            ['term' => 'chair'],
            ['term' => 'animals', 'children' => [
                ['term' => 'cat', 'children' => [
                    ['term' => 'calico'],
                ]],
            ]],
        ]);

        // 'cat' and 'calico' kept their position within their parent, so the diff
        // doesn't consider them moved. Their order still shifted, which is why
        // the descendants of a moved branch have to be updated as well.
        $this->assertOrders(['furniture' => 1, 'chair' => 2, 'animals' => 3, 'cat' => 4, 'calico' => 5]);
    }

    #[Test]
    public function reordering_a_branch_leaves_the_order_of_untouched_branches_alone()
    {
        $taxonomy = $this->makeTaxonomy();

        $this->saveTree($taxonomy, $this->nestedTree());

        $this->saveTree($taxonomy, [
            ['term' => 'animals', 'children' => [
                ['term' => 'cat', 'children' => [
                    ['term' => 'calico'],
                ]],
            ]],
            ['term' => 'chair'],
            ['term' => 'furniture'],
        ]);

        $this->assertOrders(['animals' => 1, 'cat' => 2, 'calico' => 3, 'chair' => 4, 'furniture' => 5]);
    }

    #[Test]
    public function it_diffs_a_tree_whose_branches_still_use_entry_keys()
    {
        $taxonomy = $this->makeTaxonomy();

        $this->saveTree($taxonomy, [
            ['entry' => 'categories::animals', 'children' => [
                ['entry' => 'categories::cat', 'children' => [
                    ['entry' => 'categories::calico'],
                ]],
            ]],
            ['entry' => 'categories::furniture'],
            ['entry' => 'categories::chair'],
        ]);

        // Both sides of the diff get normalized, so only 'chair' and 'furniture'
        // come out as moved rather than the whole tree being re-keyed.
        $index = $this->installOrderIndexSpy();

        $this->saveTree($taxonomy, [
            ['term' => 'animals', 'children' => [
                ['term' => 'cat', 'children' => [
                    ['term' => 'calico'],
                ]],
            ]],
            ['term' => 'chair'],
            ['term' => 'furniture'],
        ]);

        $this->assertEquals(0, $index->updates);
        $this->assertEquals(2, $index->itemUpdates);
        $this->assertOrders(['animals' => 1, 'cat' => 2, 'calico' => 3, 'chair' => 4, 'furniture' => 5]);
    }

    #[Test]
    public function it_updates_the_order_index_per_term_rather_than_rebuilding_it()
    {
        $taxonomy = $this->makeTaxonomy();

        $this->saveTree($taxonomy, $this->nestedTree());

        $index = $this->installOrderIndexSpy();

        $this->saveTree($taxonomy, [
            ['term' => 'animals', 'children' => [
                ['term' => 'cat', 'children' => [
                    ['term' => 'calico'],
                ]],
            ]],
            ['term' => 'chair'],
            ['term' => 'furniture'],
        ]);

        // Only the two swapped terms are affected, so the whole index shouldn't be
        // rebuilt. Counting the calls rather than timing them is what makes this
        // meaningful, since nothing is actually being written to disk here.
        $this->assertEquals(0, $index->updates);
        $this->assertEquals(2, $index->itemUpdates);
    }
}

class CountingOrderIndex extends Value
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

    public function resetCounts()
    {
        $this->updates = 0;
        $this->itemUpdates = 0;

        return $this;
    }
}
