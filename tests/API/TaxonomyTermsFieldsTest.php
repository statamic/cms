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

class TaxonomyTermsFieldsTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $hierarchyFields = ['parent', 'children', 'ancestors', 'depth', 'is_root'];

    public function setUp(): void
    {
        parent::setUp();

        Facades\Config::set('statamic.api.enabled', true);
        Facades\Config::set('statamic.api.resources.taxonomies', true);
    }

    private function makeHierarchicalTaxonomy()
    {
        tap(Taxonomy::make('categories')->structureContents([]))->save();

        foreach (['animals', 'cat', 'calico', 'furniture'] as $slug) {
            tap(Term::make($slug)->taxonomy('categories')->data(['title' => ucfirst($slug)]))->save();
        }

        Taxonomy::findByHandle('categories')->structure()->tree()->tree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat', 'children' => [
                    ['term' => 'calico'],
                ]],
            ]],
            ['term' => 'furniture'],
        ])->save();
    }

    private function makeOrderableTaxonomy()
    {
        tap(Taxonomy::make('ordered')->structureContents(['max_depth' => 1]))->save();

        foreach (['one', 'two'] as $slug) {
            tap(Term::make($slug)->taxonomy('ordered')->data(['title' => ucfirst($slug)]))->save();
        }

        Taxonomy::findByHandle('ordered')->structure()->tree()->tree([
            ['term' => 'one'],
            ['term' => 'two'],
        ])->save();
    }

    #[Test]
    public function it_doesnt_recurse_when_every_requested_field_is_excluded()
    {
        // https://github.com/statamic/cms/pull/15192 - requesting only excluded fields
        // used to leave an empty selection, which fell back to the full key set and
        // recursed through the term hierarchy until the stack was exhausted.
        $this->makeHierarchicalTaxonomy();

        $this->get('/api/taxonomies/categories/terms?fields=entries')->assertSuccessful();
        $this->get('/api/taxonomies/categories/terms?fields=collection')->assertSuccessful();
        $this->get('/api/taxonomies/categories/terms/cat?fields=entries')->assertSuccessful();
    }

    #[Test]
    public function hierarchy_fields_are_not_in_the_default_output()
    {
        $this->makeHierarchicalTaxonomy();

        $terms = $this->get('/api/taxonomies/categories/terms')->assertSuccessful()->json('data');
        $term = $this->get('/api/taxonomies/categories/terms/cat')->assertSuccessful()->json('data');

        foreach ($this->hierarchyFields as $field) {
            $this->assertArrayNotHasKey($field, $terms[0]);
            $this->assertArrayNotHasKey($field, $term);
        }

        $this->assertArrayHasKey('title', $term);
    }

    #[Test]
    public function hierarchy_fields_are_not_in_the_default_output_of_an_orderable_taxonomy()
    {
        $this->makeOrderableTaxonomy();

        $term = $this->get('/api/taxonomies/ordered/terms/one')->assertSuccessful()->json('data');

        foreach ($this->hierarchyFields as $field) {
            $this->assertArrayNotHasKey($field, $term);
        }
    }

    #[Test]
    public function it_only_returns_the_requested_fields()
    {
        $this->makeHierarchicalTaxonomy();

        $terms = $this->get('/api/taxonomies/categories/terms?fields=id')->assertSuccessful()->json('data');

        $this->assertEquals(['id' => 'categories::animals'], $terms[0]);
    }

    #[Test]
    public function it_only_returns_the_requested_fields_for_a_single_term()
    {
        $this->makeHierarchicalTaxonomy();

        $term = $this->get('/api/taxonomies/categories/terms/cat?fields=title')->assertSuccessful()->json('data');

        $this->assertEquals(['title' => 'Cat'], $term);
    }

    #[Test]
    public function it_returns_a_shallow_parent_when_requested()
    {
        $this->makeHierarchicalTaxonomy();

        $terms = $this->get('/api/taxonomies/categories/terms?fields=title,parent')->assertSuccessful()->json('data');

        $this->assertEquals(['title' => 'Animals', 'parent' => null], $terms[0]);

        $this->assertEquals('Cat', $terms[1]['title']);
        $this->assertEquals([
            'id' => 'categories::animals',
            'title' => 'Animals',
            'slug' => 'animals',
            'url' => '/categories/animals',
            'permalink' => 'http://localhost/categories/animals',
            'api_url' => 'http://localhost/api/taxonomies/categories/terms/animals',
        ], $terms[1]['parent']);
    }

    #[Test]
    public function requesting_a_parent_doesnt_leak_fields_onto_the_other_terms()
    {
        // Resolving a parent looks the term up, which resets the columns selected by
        // the original query. The requested fields need to survive that.
        $this->makeHierarchicalTaxonomy();

        $terms = $this->get('/api/taxonomies/categories/terms?fields=title,parent')->assertSuccessful()->json('data');

        foreach ($terms as $term) {
            $this->assertEquals(['title', 'parent'], array_keys($term));
        }
    }

    #[Test]
    public function it_doesnt_return_children_or_ancestors_when_requested()
    {
        // Matching entries, where a flat listing stays flat. The tree endpoint is
        // where a structure gets traversed.
        $this->makeHierarchicalTaxonomy();

        $terms = $this->get('/api/taxonomies/categories/terms?fields=title,children,ancestors')
            ->assertSuccessful()->json('data');

        $this->assertEquals(['title' => 'Animals', 'children' => null, 'ancestors' => null], $terms[0]);
        $this->assertEquals(['title' => 'Calico', 'children' => null, 'ancestors' => null], $terms[2]);
    }

    #[Test]
    public function it_returns_the_depth_when_requested()
    {
        $this->makeHierarchicalTaxonomy();

        $term = $this->get('/api/taxonomies/categories/terms/calico?fields=depth,is_root')
            ->assertSuccessful()->json('data');

        $this->assertEquals(['depth' => 3, 'is_root' => false], $term);
    }

    #[Test]
    public function a_flat_taxonomy_returns_user_defined_fields_with_reserved_hierarchy_handles()
    {
        $blueprint = Blueprint::makeFromFields([
            'parent' => ['type' => 'text'],
            'children' => ['type' => 'text'],
            'ancestors' => ['type' => 'text'],
            'depth' => ['type' => 'text'],
            'is_root' => ['type' => 'text'],
        ])->setHandle('tags');
        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect(['tags' => $blueprint]));

        tap(Taxonomy::make('tags'))->save();

        tap(Term::make('red')->taxonomy('tags')->blueprint('tags')->data([
            'title' => 'Red',
            'parent' => 'Colours',
            'children' => 'Crimson, Scarlet',
            'ancestors' => 'Warm colours',
            'depth' => 'Quite deep',
            'is_root' => 'Nope',
        ]))->save();

        $term = $this->get('/api/taxonomies/tags/terms/red')->assertSuccessful()->json('data');

        $this->assertEquals('Colours', $term['parent']);
        $this->assertEquals('Crimson, Scarlet', $term['children']);
        $this->assertEquals('Warm colours', $term['ancestors']);
        $this->assertEquals('Quite deep', $term['depth']);
        $this->assertEquals('Nope', $term['is_root']);
    }
}
