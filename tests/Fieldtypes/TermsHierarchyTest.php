<?php

namespace Tests\Fieldtypes;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Facades\Antlers;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TermsHierarchyTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Facades\Collection::make('blog')->taxonomies(['categories'])->save();

        tap(Facades\Taxonomy::make('categories')->structureContents([]))->save();

        foreach (['animals', 'cat', 'furniture'] as $slug) {
            tap(Term::make($slug)->taxonomy('categories')->data(['title' => ucfirst($slug)]))->save();
        }

        Facades\Taxonomy::findByHandle('categories')->structure()->tree()->tree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
            ]],
            ['term' => 'furniture'],
        ])->save();

        $this->actingAs(tap(User::make()->makeSuper())->save());
    }

    #[Test]
    public function it_identifies_the_hierarchical_taxonomy()
    {
        $this->assertNotNull($this->fieldtype(['taxonomies' => ['categories']])->hierarchicalTaxonomy());
        $this->assertNull($this->fieldtype(['taxonomies' => ['categories', 'other']])->hierarchicalTaxonomy());
        $this->assertTrue($this->fieldtype(['taxonomies' => ['categories', 'other']])->hasHierarchicalTaxonomy());
    }

    #[Test]
    public function preload_includes_tree_meta_for_a_hierarchical_taxonomy()
    {
        $preload = $this->fieldtype(['taxonomies' => ['categories']])->preload();

        $this->assertArrayHasKey('tree', $preload);
        $this->assertEquals('select', $preload['mode']);
        $this->assertEquals('http://localhost/cp/taxonomies/categories/tree', $preload['tree']['url']);
        $this->assertFalse($preload['tree']['expectsRoot']);
    }

    #[Test]
    public function preload_has_no_tree_meta_for_a_flat_taxonomy()
    {
        tap(Facades\Taxonomy::make('tags'))->save();

        $preload = $this->fieldtype(['taxonomies' => ['tags']])->preload();

        $this->assertArrayNotHasKey('tree', $preload);
        $this->assertArrayNotHasKey('mode', $preload);
    }

    #[Test]
    public function preload_respects_an_explicit_mode()
    {
        $preload = $this->fieldtype(['taxonomies' => ['categories'], 'mode' => 'default'])->preload();

        $this->assertEquals('select', $preload['mode']);

        $preload = $this->fieldtype(['taxonomies' => ['categories'], 'mode' => 'typeahead'])->preload();

        $this->assertArrayNotHasKey('mode', $preload);

        tap(Facades\Taxonomy::make('tags'))->save();

        $preload = $this->fieldtype(['taxonomies' => ['categories', 'tags'], 'mode' => 'default'])->preload();

        $this->assertEquals('select', $preload['mode']);
    }

    #[Test]
    public function the_item_hint_is_the_ancestor_path()
    {
        $fieldtype = $this->fieldtype(['taxonomies' => ['categories']]);

        $cat = Term::find('categories::cat')->in('en');
        $animals = Term::find('categories::animals')->in('en');

        $this->assertEquals('Animals', $fieldtype->getItemHint($cat));
        $this->assertEquals('', $fieldtype->getItemHint($animals));
    }

    #[Test]
    public function item_data_includes_depth_and_path_for_hierarchical_terms()
    {
        $item = $this->fieldtype(['taxonomies' => ['categories']])->getItemData(['cat'])->first();

        $this->assertEquals(2, $item['depth']);
        $this->assertEquals('animals>cat', $item['path']);
        $this->assertEquals(['Animals'], $item['ancestors']);
        $this->assertArrayNotHasKey('taxonomy_title', $item);
        $this->assertEquals('Animals', $item['hint']);
    }

    #[Test]
    public function select_options_include_depth_and_path_even_when_mode_is_default()
    {
        $fieldtype = $this->fieldtype(['taxonomies' => ['categories']]);
        $request = new Request(['paginate' => false]);
        $items = $fieldtype->getIndexItems($request);
        $resolved = json_decode(
            $fieldtype->getResourceCollection($request, $items)->toResponse($request)->getContent(),
            true
        )['data'];

        $byId = collect($resolved)->keyBy(fn ($term) => $term['id']);

        $this->assertEquals(1, $byId['categories::animals']['depth']);
        $this->assertEquals('animals', $byId['categories::animals']['path']);
        $this->assertEquals([], $byId['categories::animals']['ancestors']);
        $this->assertArrayNotHasKey('hint', $byId['categories::animals']);

        $this->assertEquals(2, $byId['categories::cat']['depth']);
        $this->assertEquals('animals>cat', $byId['categories::cat']['path']);
        $this->assertEquals(['Animals'], $byId['categories::cat']['ancestors']);
        $this->assertEquals('Animals', $byId['categories::cat']['hint']);
    }

    #[Test]
    public function select_options_stay_nested_when_multiple_taxonomies_are_configured()
    {
        tap(Facades\Taxonomy::make('tags'))->save();
        tap(Term::make('featured')->taxonomy('tags')->data(['title' => 'Featured']))->save();

        $fieldtype = $this->fieldtype(['taxonomies' => ['categories', 'tags'], 'mode' => 'select']);
        $request = new Request(['paginate' => false]);
        $items = $fieldtype->getIndexItems($request);
        $resolved = json_decode(
            $fieldtype->getResourceCollection($request, $items)->toResponse($request)->getContent(),
            true
        )['data'];

        $byId = collect($resolved)->keyBy(fn ($term) => $term['id']);

        $this->assertEquals(2, $byId['categories::cat']['depth']);
        $this->assertEquals('animals>cat', $byId['categories::cat']['path']);
        $this->assertEquals(['Animals'], $byId['categories::cat']['ancestors']);
        $this->assertEquals('Categories', $byId['categories::cat']['taxonomy_title']);
        $this->assertEquals('Categories • Animals', $byId['categories::cat']['hint']);
        $this->assertArrayNotHasKey('depth', $byId['tags::featured']);
        $this->assertEquals('Tags', $byId['tags::featured']['taxonomy_title']);
        $this->assertEquals('Tags', $byId['tags::featured']['hint']);
    }

    #[Test]
    public function item_data_includes_depth_when_multiple_taxonomies_are_configured()
    {
        tap(Facades\Taxonomy::make('tags'))->save();

        $item = $this->fieldtype(['taxonomies' => ['categories', 'tags']])->getItemData(['categories::cat'])->first();

        $this->assertEquals(2, $item['depth']);
        $this->assertEquals('animals>cat', $item['path']);
        $this->assertEquals(['Animals'], $item['ancestors']);
        $this->assertEquals('Categories', $item['taxonomy_title']);
        $this->assertEquals('Categories • Animals', $item['hint']);
    }

    #[Test]
    public function processing_a_path_creates_missing_terms_and_grafts_them_into_the_tree()
    {
        $fieldtype = $this->fieldtype(['taxonomies' => ['categories']]);

        $processed = $fieldtype->process(['animals > cat > calico']);

        $this->assertEquals(['calico'], $processed);

        $calico = Term::find('categories::calico');
        $this->assertNotNull($calico);
        $this->assertEquals('calico', $calico->title());

        $tree = Facades\Taxonomy::findByHandle('categories')->structure()->tree()->tree();

        $this->assertEquals([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat', 'children' => [
                    ['term' => 'calico'],
                ]],
            ]],
            ['term' => 'furniture'],
        ], $tree);
    }

    #[Test]
    public function processing_a_path_reuses_existing_terms_in_place()
    {
        $fieldtype = $this->fieldtype(['taxonomies' => ['categories']]);

        // "cat" already lives under "animals" and shouldn't get re-parented under "furniture".
        $processed = $fieldtype->process(['furniture > cat']);

        $this->assertEquals(['cat'], $processed);

        $tree = Facades\Taxonomy::findByHandle('categories')->structure()->tree()->tree();

        $this->assertEquals([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
            ]],
            ['term' => 'furniture'],
        ], $tree);
    }

    #[Test]
    public function processing_a_path_deeper_than_max_depth_fails_validation()
    {
        tap(Facades\Taxonomy::findByHandle('categories')->structureContents(['max_depth' => 2]))->save();

        $this->expectException(ValidationException::class);

        $this->fieldtype(['taxonomies' => ['categories']])->process(['animals > cat > calico']);
    }

    #[Test]
    public function processing_a_plain_value_still_creates_a_root_term()
    {
        $processed = $this->fieldtype(['taxonomies' => ['categories']])->process(['Plants']);

        $this->assertEquals(['plants'], $processed);
        $this->assertNotNull(Term::find('categories::plants'));
    }

    #[Test]
    public function processing_a_path_of_new_terms_nests_them_in_the_persisted_tree()
    {
        $processed = $this->fieldtype(['taxonomies' => ['categories']])->process(['plants > fern']);

        $this->assertEquals(['fern'], $processed);
        $this->assertNotNull(Term::find('categories::plants'));
        $this->assertNotNull(Term::find('categories::fern'));

        $tree = Facades\Taxonomy::findByHandle('categories')->structure()->tree()->tree();

        $this->assertEquals([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
            ]],
            ['term' => 'furniture'],
            ['term' => 'plants', 'children' => [
                ['term' => 'fern'],
            ]],
        ], $tree);
    }

    #[Test]
    public function processing_a_string_matching_an_existing_term_selects_it_instead_of_creating_a_path()
    {
        tap(Term::make('ages-21')->taxonomy('categories')->data(['title' => 'Ages > 21']))->save();

        $processed = $this->fieldtype(['taxonomies' => ['categories']])->process(['Ages > 21']);

        $this->assertEquals(['ages-21'], $processed);
        $this->assertNull(Term::find('categories::ages'));
        $this->assertNull(Term::find('categories::21'));
    }

    #[Test]
    public function a_slash_no_longer_creates_a_path_since_it_is_not_the_delimiter()
    {
        $processed = $this->fieldtype(['taxonomies' => ['categories']])->process(['AC/DC']);

        $this->assertEquals(['acdc'], $processed);
        $this->assertNull(Term::find('categories::ac'));
        $this->assertNull(Term::find('categories::dc'));
        $this->assertEquals('AC/DC', Term::find('categories::acdc')->title());
    }

    #[Test]
    public function processing_a_path_with_no_matching_term_still_creates_the_nested_path()
    {
        $processed = $this->fieldtype(['taxonomies' => ['categories']])->process(['animals > kitten']);

        $this->assertEquals(['kitten'], $processed);

        $tree = Facades\Taxonomy::findByHandle('categories')->structure()->tree()->tree();

        $this->assertEquals([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
                ['term' => 'kitten'],
            ]],
            ['term' => 'furniture'],
        ], $tree);
    }

    #[Test]
    public function processing_a_path_matching_an_unrelated_existing_term_selects_it_instead_of_creating_the_path()
    {
        // Documents the accepted trade-off: an existing whole-string slug match wins over path
        // parsing, even when the typed value looks like a path. Here "animals>cat" slugifies to
        // "animalscat" (the delimiter is stripped, not converted to a separator), which happens
        // to already exist as an unrelated term, so it's selected instead of creating a nested path.
        tap(Term::make('animalscat')->taxonomy('categories')->data(['title' => 'Animals Cat']))->save();

        $processed = $this->fieldtype(['taxonomies' => ['categories']])->process(['animals>cat']);

        $this->assertEquals(['animalscat'], $processed);
    }

    #[Test]
    public function flat_taxonomy_terms_containing_the_delimiter_are_unaffected_by_path_lookup_order()
    {
        tap(Facades\Taxonomy::make('tags'))->save();

        $processed = $this->fieldtype(['taxonomies' => ['tags']])->process(['Ages > 21']);

        $this->assertEquals(['ages-21'], $processed);
        $this->assertEquals('Ages > 21', Term::find('tags::ages-21')->title());
    }

    #[Test]
    public function a_raw_stored_path_value_augments_and_renders_correctly()
    {
        // Simulates a value written programmatically (not through the CP fieldtype form),
        // e.g. `$entry->set('categories', ['animals > cat'])->save()`, which persists the
        // raw path string rather than the resolved leaf slug.
        $entry = EntryFactory::collection('blog')->id('augment-test')->create();

        $fieldtype = $this->fieldtype(['taxonomies' => ['categories']], $entry);

        $augmented = $fieldtype->augment(['animals > cat'])->get();

        $this->assertCount(1, $augmented);
        $this->assertEquals('categories::cat', $augmented->first()->id());
        $this->assertEquals('Cat', $augmented->first()->title());

        $rendered = Antlers::parse('{{ categories }}{{ title }}{{ /categories }}', [
            'categories' => $augmented,
        ]);

        $this->assertEquals('Cat', (string) $rendered);
    }

    #[Test]
    public function parent_field_index_query_excludes_configured_ids()
    {
        $items = $this->fieldtype([
            'taxonomies' => ['categories'],
            'exclusions' => ['categories::animals', 'categories::cat'],
        ])->getIndexItems(new Request(['paginate' => false]));

        $this->assertEquals(['categories::furniture'], $items->map->id()->all());
    }

    public function fieldtype($config = [], $parent = null)
    {
        $field = new Field('test', array_merge(['type' => 'terms'], $config));

        $field->setParent($parent ?? EntryFactory::collection('blog')->create());

        return (new \Statamic\Fieldtypes\Terms)->setField($field);
    }
}
