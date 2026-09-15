<?php

namespace Tests\Fieldtypes;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Statamic\Fields\Field;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TermsDescendantSearchTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Facades\Collection::make('blog')->taxonomies(['categories', 'tags'])->save();

        tap(Facades\Taxonomy::make('categories')->structureContents([]))->save();
        tap(Facades\Taxonomy::make('tags'))->save();

        foreach (['animals', 'cat', 'calico', 'furniture'] as $slug) {
            tap(Term::make($slug)->taxonomy('categories')->data(['title' => ucfirst($slug)]))->save();
        }

        foreach (['animals-tag', 'unrelated'] as $slug) {
            tap(Term::make($slug)->taxonomy('tags')->data(['title' => ucfirst($slug)]))->save();
        }

        Facades\Taxonomy::findByHandle('categories')->structure()->tree()->tree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat', 'children' => [
                    ['term' => 'calico'],
                ]],
            ]],
            ['term' => 'furniture'],
        ])->save();

        $this->actingAs(tap(User::make()->makeSuper())->save());
    }

    #[Test]
    public function searching_a_parent_surfaces_its_descendants()
    {
        $ids = $this->searchIds('animals');

        $this->assertEqualsCanonicalizing(
            ['categories::animals', 'categories::cat', 'categories::calico'],
            $ids
        );
    }

    #[Test]
    public function descendants_are_surfaced_to_every_depth()
    {
        // Ancestors are not. A breadcrumb only points upwards, so matching "Cat" says
        // nothing about Animals.
        $this->assertEqualsCanonicalizing(
            ['categories::cat', 'categories::calico'],
            $this->searchIds('cat')
        );
    }

    #[Test]
    public function a_non_matching_search_still_returns_nothing()
    {
        $this->assertEquals([], $this->searchIds('zzzz'));
    }

    #[Test]
    public function a_term_outside_the_tree_is_matched_on_its_title_alone()
    {
        tap(Term::make('orphan')->taxonomy('categories')->data(['title' => 'Orphan']))->save();

        $this->assertEquals(['categories::orphan'], $this->searchIds('orphan'));
    }

    #[Test]
    public function a_flat_taxonomy_is_unaffected()
    {
        $this->assertEquals(['tags::animals-tag'], $this->searchIds('animals', ['tags']));
    }

    #[Test]
    public function a_flat_taxonomy_alongside_a_hierarchical_one_only_expands_the_hierarchical_side()
    {
        $this->assertEqualsCanonicalizing(
            ['categories::animals', 'categories::cat', 'categories::calico', 'tags::animals-tag'],
            $this->searchIds('animals', ['categories', 'tags'])
        );
    }

    #[Test]
    public function a_slug_colliding_across_taxonomies_is_not_dragged_in()
    {
        // Slugs are only unique within a taxonomy, so the subtree has to be expanded into
        // scoped ids rather than bare slugs.
        tap(Term::make('cat')->taxonomy('tags')->data(['title' => 'Unrelated Cat Tag']))->save();

        $this->assertNotContains('tags::cat', $this->searchIds('animals', ['categories', 'tags']));
    }

    #[Test]
    public function an_excluded_parent_still_surfaces_its_descendants()
    {
        // A parent that's already been selected is no longer an option, but typing it should
        // still find what's underneath it.
        $ids = $this->searchIds('animals', ['categories'], ['exclusions' => ['categories::animals']]);

        $this->assertEqualsCanonicalizing(['categories::cat', 'categories::calico'], $ids);
    }

    #[Test]
    public function ancestors_are_matched_on_the_requested_sites_titles()
    {
        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]);

        tap(Facades\Taxonomy::make('topics')->sites(['en', 'fr'])->structureContents([]))->save();

        $parent = Term::make('parent')->taxonomy('topics');
        $parent->dataForLocale('en', ['title' => 'Vehicles']);
        $parent->dataForLocale('fr', ['title' => 'Vehicules']);
        $parent->save();

        $child = Term::make('child')->taxonomy('topics');
        $child->dataForLocale('en', ['title' => 'Car']);
        $child->dataForLocale('fr', ['title' => 'Voiture']);
        $child->save();

        Facades\Taxonomy::findByHandle('topics')->structure()->tree()->tree([
            ['term' => 'parent', 'children' => [
                ['term' => 'child'],
            ]],
        ])->save();

        $this->assertEqualsCanonicalizing(
            ['topics::parent', 'topics::child'],
            $this->searchIds('vehicles', ['topics'], ['site' => 'en'])
        );

        $this->assertEqualsCanonicalizing(
            ['topics::parent', 'topics::child'],
            $this->searchIds('vehicules', ['topics'], ['site' => 'fr'])
        );

        // The English title finds nothing in French. The ancestor lookup reads the requested
        // site's titles, not the default locale's.
        $this->assertEquals([], $this->searchIds('vehicles', ['topics'], ['site' => 'fr']));
    }

    private function searchIds(string $search, array $taxonomies = ['categories'], array $params = [])
    {
        $fieldtype = $this->fieldtype(['taxonomies' => $taxonomies, 'mode' => 'typeahead']);

        $request = new Request([...$params, 'paginate' => false, 'search' => $search]);

        // A resource resolves its request out of the container rather than the one handed to
        // toResponse(), which in a real CP request is the same one the items were queried with.
        $this->app->instance('request', $request);

        return $fieldtype->getIndexItems($request)->map->id()->values()->all();
    }

    private function fieldtype($config = [], $parent = null)
    {
        $field = new Field('test', array_merge(['type' => 'terms'], $config));

        $field->setParent($parent ?? EntryFactory::collection('blog')->create());

        return (new \Statamic\Fieldtypes\Terms)->setField($field);
    }
}
