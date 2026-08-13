<?php

namespace Tests\Fieldtypes;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
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
    public function processing_a_path_creates_missing_terms_and_grafts_them_into_the_tree()
    {
        $fieldtype = $this->fieldtype(['taxonomies' => ['categories']]);

        $processed = $fieldtype->process(['animals/cat/calico']);

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
        $processed = $fieldtype->process(['furniture/cat']);

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

        $this->fieldtype(['taxonomies' => ['categories']])->process(['animals/cat/calico']);
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
        $processed = $this->fieldtype(['taxonomies' => ['categories']])->process(['plants/fern']);

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

    public function fieldtype($config = [], $parent = null)
    {
        $field = new Field('test', array_merge(['type' => 'terms'], $config));

        $field->setParent($parent ?? EntryFactory::collection('blog')->create());

        return (new \Statamic\Fieldtypes\Terms)->setField($field);
    }
}
