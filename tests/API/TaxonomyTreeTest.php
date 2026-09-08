<?php

namespace Tests\API;

use Facades\Statamic\Fields\BlueprintRepository;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TaxonomyTreeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Facades\Config::set('statamic.api.enabled', true);
        Facades\Config::set('statamic.api.resources.taxonomies', true);
    }

    private function makeHierarchicalTaxonomy()
    {
        tap(Taxonomy::make('categories')->structureContents([]))->save();

        foreach (['animals', 'cat', 'furniture'] as $slug) {
            tap(Term::make($slug)->taxonomy('categories')->data(['title' => ucfirst($slug)]))->save();
        }

        Taxonomy::findByHandle('categories')->structure()->tree()->tree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
            ]],
            ['term' => 'furniture'],
        ])->save();
    }

    #[Test]
    public function it_gets_the_taxonomy_tree()
    {
        $this->makeHierarchicalTaxonomy();

        $response = $this->get('/api/taxonomies/categories/tree')->assertSuccessful();

        $tree = $response->json('data');

        $this->assertCount(2, $tree);
        $this->assertEquals('categories::animals', $tree[0]['term']['id']);
        $this->assertEquals(1, $tree[0]['depth']);
        $this->assertEquals('categories::cat', $tree[0]['children'][0]['term']['id']);
        $this->assertEquals(2, $tree[0]['children'][0]['depth']);
        $this->assertEquals('categories::furniture', $tree[1]['term']['id']);
    }

    #[Test]
    public function it_limits_the_tree_by_max_depth()
    {
        $this->makeHierarchicalTaxonomy();

        $tree = $this->get('/api/taxonomies/categories/tree?max_depth=1')->assertSuccessful()->json('data');

        $this->assertCount(2, $tree);
        $this->assertEquals([], $tree[0]['children']);
    }

    #[Test]
    public function it_404s_for_a_flat_taxonomy()
    {
        tap(Taxonomy::make('tags'))->save();

        $this->get('/api/taxonomies/tags/tree')->assertNotFound();
    }

    #[Test]
    public function terms_include_parent_and_depth_when_requested()
    {
        $this->makeHierarchicalTaxonomy();

        $url = '/api/taxonomies/categories/terms/%s?fields=depth,is_root,parent';

        $term = $this->get(sprintf($url, 'cat'))->assertSuccessful()->json('data');

        $this->assertEquals(2, $term['depth']);
        $this->assertFalse($term['is_root']);
        $this->assertEquals('categories::animals', $term['parent']['id']);

        $root = $this->get(sprintf($url, 'animals'))->assertSuccessful()->json('data');

        $this->assertEquals(1, $root['depth']);
        $this->assertTrue($root['is_root']);
        $this->assertNull($root['parent']);
    }

    #[Test]
    public function tree_terms_dont_repeat_the_hierarchy_the_tree_already_describes()
    {
        $this->makeHierarchicalTaxonomy();

        $tree = $this->get('/api/taxonomies/categories/tree')->assertSuccessful()->json('data');

        foreach (['parent', 'children', 'ancestors', 'depth', 'is_root'] as $field) {
            $this->assertArrayNotHasKey($field, $tree[0]['term']);
            $this->assertArrayNotHasKey($field, $tree[0]['children'][0]['term']);
        }
    }

    #[Test]
    public function it_selects_fields_on_tree_terms()
    {
        $this->makeHierarchicalTaxonomy();

        $tree = $this->get('/api/taxonomies/categories/tree?fields=title')->assertSuccessful()->json('data');

        $this->assertEquals(['title' => 'Animals'], $tree[0]['term']);
        $this->assertEquals(['title' => 'Cat'], $tree[0]['children'][0]['term']);
        $this->assertEquals(2, $tree[0]['children'][0]['depth']);
    }

    #[Test]
    public function flat_taxonomy_exposes_user_defined_fields_with_reserved_hierarchy_handles()
    {
        $blueprint = Blueprint::makeFromFields([
            'parent' => ['type' => 'text'],
            'children' => ['type' => 'text'],
            'ancestors' => ['type' => 'text'],
        ])->setHandle('tags');
        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect(['tags' => $blueprint]));

        tap(Taxonomy::make('tags'))->save();

        tap(Term::make('red')->taxonomy('tags')->blueprint('tags')->data([
            'title' => 'Red',
            'parent' => 'Colours',
            'children' => 'Crimson, Scarlet',
            'ancestors' => 'Warm colours',
        ]))->save();

        $term = $this->get('/api/taxonomies/tags/terms/red')->assertSuccessful()->json('data');

        $this->assertEquals('Colours', $term['parent']);
        $this->assertEquals('Crimson, Scarlet', $term['children']);
        $this->assertEquals('Warm colours', $term['ancestors']);
    }
}
