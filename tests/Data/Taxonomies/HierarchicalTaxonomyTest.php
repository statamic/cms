<?php

namespace Tests\Data\Taxonomies;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Structures\TaxonomyStructure;
use Statamic\Structures\TaxonomyTree;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class HierarchicalTaxonomyTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function makeHierarchicalTaxonomy()
    {
        $taxonomy = tap(Taxonomy::make('categories')->title('Categories')->structureContents([]))->save();

        foreach (['animals', 'cat', 'calico', 'furniture'] as $slug) {
            tap(Term::make($slug)->taxonomy('categories')->data(['title' => ucfirst($slug)]))->save();
        }

        $taxonomy->structure()->tree()->tree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat', 'children' => [
                    ['term' => 'calico'],
                ]],
            ]],
            ['term' => 'furniture'],
        ])->save();

        return $taxonomy;
    }

    #[Test]
    public function a_taxonomy_without_structure_is_not_hierarchical()
    {
        $taxonomy = tap(Taxonomy::make('tags'))->save();

        $this->assertFalse($taxonomy->hasStructure());
        $this->assertFalse($taxonomy->hierarchical());
        $this->assertFalse($taxonomy->orderable());
        $this->assertNull($taxonomy->structure());
    }

    #[Test]
    public function a_taxonomy_with_structure_is_hierarchical()
    {
        $taxonomy = tap(Taxonomy::make('categories')->structureContents(['max_depth' => 3]))->save();

        $this->assertTrue($taxonomy->hasStructure());
        $this->assertTrue($taxonomy->hierarchical());
        $this->assertFalse($taxonomy->orderable());
        $this->assertInstanceOf(TaxonomyStructure::class, $structure = $taxonomy->structure());
        $this->assertEquals(3, $structure->maxDepth());
        $this->assertFalse($structure->expectsRoot());
        $this->assertInstanceOf(TaxonomyTree::class, $structure->tree());
    }

    #[Test]
    public function a_taxonomy_with_max_depth_of_one_is_orderable_but_not_hierarchical()
    {
        $taxonomy = tap(Taxonomy::make('categories')->structureContents(['max_depth' => 1]))->save();

        $this->assertTrue($taxonomy->hasStructure());
        $this->assertFalse($taxonomy->hierarchical());
        $this->assertTrue($taxonomy->orderable());
    }

    #[Test]
    public function it_gets_hierarchy_from_the_tree()
    {
        $this->makeHierarchicalTaxonomy();

        $calico = Term::find('categories::calico');
        $animals = Term::find('categories::animals');

        $this->assertEquals(3, $calico->depth());
        $this->assertEquals('categories::cat', $calico->parent()->id());
        $this->assertEquals(['animals', 'cat'], $calico->ancestors()->map->slug()->all());
        $this->assertEquals(['cat'], $animals->in('en')->children()->map->slug()->all());

        $this->assertEquals(1, $animals->depth());
        $this->assertNull($animals->in('en')->parent());
    }

    #[Test]
    public function hierarchical_terms_get_nested_uris()
    {
        $this->makeHierarchicalTaxonomy();

        $this->assertEquals('/categories/animals', Term::find('categories::animals')->uri());
        $this->assertEquals('/categories/animals/cat', Term::find('categories::cat')->uri());
        $this->assertEquals('/categories/animals/cat/calico', Term::find('categories::calico')->uri());
        $this->assertEquals('/categories/furniture', Term::find('categories::furniture')->uri());
    }

    #[Test]
    public function it_finds_hierarchical_terms_by_nested_uri()
    {
        $this->makeHierarchicalTaxonomy();

        $term = Term::findByUri('/categories/animals/cat/calico');

        $this->assertNotNull($term);
        $this->assertEquals('categories::calico', $term->id());
    }

    #[Test]
    public function it_finds_hierarchical_terms_by_flat_uri_for_redirecting()
    {
        $this->makeHierarchicalTaxonomy();

        $term = Term::findByUri('/categories/calico');

        $this->assertNotNull($term);
        $this->assertEquals('categories::calico', $term->id());
    }

    #[Test]
    public function it_doesnt_find_terms_by_nested_uri_on_flat_taxonomies()
    {
        tap(Taxonomy::make('tags'))->save();
        tap(Term::make('foo')->taxonomy('tags')->data([]))->save();

        $this->assertNotNull(Term::findByUri('/tags/foo'));
        $this->assertNull(Term::findByUri('/tags/nested/foo'));
    }

    #[Test]
    public function validating_a_tree_appends_missing_terms()
    {
        $taxonomy = $this->makeHierarchicalTaxonomy();

        tap(Term::make('dog')->taxonomy('categories')->data(['title' => 'Dog']))->save();

        $tree = $taxonomy->structure()->validateTree([
            ['term' => 'animals'],
        ], 'en');

        $slugs = collect($tree)->pluck('term')->all();

        $this->assertContains('animals', $slugs);
        $this->assertContains('dog', $slugs);
        $this->assertContains('furniture', $slugs);
    }

    #[Test]
    public function validating_a_tree_removes_non_existent_terms()
    {
        $taxonomy = $this->makeHierarchicalTaxonomy();

        $tree = $taxonomy->structure()->validateTree([
            ['term' => 'animals'],
            ['term' => 'nonexistent'],
            ['term' => 'cat'],
            ['term' => 'calico'],
            ['term' => 'furniture'],
        ], 'en');

        $this->assertNotContains('nonexistent', collect($tree)->pluck('term')->all());
    }

    #[Test]
    public function validating_a_tree_drops_duplicate_terms_keeping_the_first()
    {
        $taxonomy = $this->makeHierarchicalTaxonomy();

        $tree = $taxonomy->structure()->validateTree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
            ]],
            ['term' => 'animals'],
            ['term' => 'furniture'],
            ['term' => 'calico'],
        ], 'en');

        $this->assertEquals([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
            ]],
            ['term' => 'furniture'],
            ['term' => 'calico'],
        ], $tree);
    }

    #[Test]
    public function validating_a_tree_normalizes_entry_keys_and_full_term_ids()
    {
        $taxonomy = $this->makeHierarchicalTaxonomy();

        $tree = $taxonomy->structure()->validateTree([
            ['entry' => 'categories::animals', 'children' => [
                ['entry' => 'categories::cat', 'children' => [
                    ['term' => 'categories::calico'],
                ]],
            ]],
            ['term' => 'furniture'],
            ['term' => 'animals'],
            ['term' => 'cat'],
            ['term' => 'calico'],
        ], 'en');

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
    public function appending_a_term_stores_the_slug_under_the_term_key()
    {
        $taxonomy = $this->makeHierarchicalTaxonomy();
        $term = Term::find('categories::furniture');

        $tree = $taxonomy->structure()->tree();
        $tree->tree([
            ['term' => 'animals'],
        ]);
        $tree->append($term);

        $this->assertEquals([
            ['term' => 'animals'],
            ['term' => 'furniture'],
        ], $tree->fileData()['tree']);
    }

    #[Test]
    public function it_gets_the_term_parent_uri()
    {
        $taxonomy = $this->makeHierarchicalTaxonomy();

        $structure = $taxonomy->structure();

        $this->assertEquals('', $structure->termParentUri(Term::find('categories::animals')->in('en')));
        $this->assertEquals('animals', $structure->termParentUri(Term::find('categories::cat')->in('en')));
        $this->assertEquals('animals/cat', $structure->termParentUri(Term::find('categories::calico')->in('en')));
    }

    #[Test]
    public function deleting_a_term_removes_its_branch_and_promotes_children()
    {
        $taxonomy = $this->makeHierarchicalTaxonomy();

        Term::find('categories::cat')->delete();

        $tree = $taxonomy->structure()->tree()->tree();

        $this->assertEquals([
            ['term' => 'animals', 'children' => [
                ['term' => 'calico'],
            ]],
            ['term' => 'furniture'],
        ], $tree);
    }

    #[Test]
    public function renaming_a_term_slug_updates_the_tree()
    {
        $taxonomy = $this->makeHierarchicalTaxonomy();

        $term = Term::find('categories::cat');
        $term->slug('feline');
        $term->save();

        $tree = $taxonomy->structure()->tree()->tree();

        $this->assertEquals([
            ['term' => 'animals', 'children' => [
                ['term' => 'feline', 'children' => [
                    ['term' => 'calico'],
                ]],
            ]],
            ['term' => 'furniture'],
        ], $tree);
    }

    #[Test]
    public function deleting_a_taxonomy_deletes_its_tree()
    {
        $taxonomy = $this->makeHierarchicalTaxonomy();

        $tree = $taxonomy->structure()->tree();

        $taxonomy->delete();

        $this->assertNull(\Statamic\Facades\Blink::store()->get('taxonomy-structure-tree-categories'));
    }

    #[Test]
    public function augmented_term_includes_hierarchy_keys()
    {
        $this->makeHierarchicalTaxonomy();

        $augmented = Term::find('categories::calico')->in('en')->toAugmentedArray(['parent', 'ancestors', 'children', 'depth', 'is_root']);

        $this->assertEquals('categories::cat', $augmented['parent']->value()->id());
        $this->assertEquals(3, $augmented['depth']->value());
        $this->assertFalse($augmented['is_root']->value());
        $this->assertCount(2, $augmented['ancestors']->value());
        $this->assertCount(0, $augmented['children']->value());
    }

    #[Test]
    public function validating_a_tree_rejects_branches_deeper_than_max_depth()
    {
        $taxonomy = tap(Taxonomy::make('categories')->structureContents(['max_depth' => 2]))->save();

        foreach (['animals', 'cat', 'calico'] as $slug) {
            tap(Term::make($slug)->taxonomy('categories')->data(['title' => ucfirst($slug)]))->save();
        }

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $taxonomy->structure()->validateTree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat', 'children' => [
                    ['term' => 'calico'],
                ]],
            ]],
        ], 'en');
    }

    #[Test]
    public function grafting_a_term_rejects_a_parent_at_max_depth()
    {
        $taxonomy = tap(Taxonomy::make('categories')->structureContents(['max_depth' => 2]))->save();

        foreach (['animals', 'cat', 'dog'] as $slug) {
            tap(Term::make($slug)->taxonomy('categories')->data(['title' => ucfirst($slug)]))->save();
        }

        $taxonomy->structure()->tree()->tree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
            ]],
        ])->save();

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $taxonomy->structure()->graftTerm('dog', 'cat');
    }
}
