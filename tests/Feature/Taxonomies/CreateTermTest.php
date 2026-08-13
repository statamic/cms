<?php

namespace Tests\Feature\Taxonomies;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CreateTermTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function makeHierarchicalTaxonomy()
    {
        $taxonomy = tap(Taxonomy::make('categories')->title('Categories')->structureContents([]))->save();

        foreach (['animals', 'cat'] as $slug) {
            tap(Term::make($slug)->taxonomy('categories')->data(['title' => ucfirst($slug)]))->save();
        }

        $taxonomy->structure()->tree()->tree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
            ]],
        ])->save();

        return $taxonomy;
    }

    #[Test]
    public function creating_a_child_term_prefills_the_parent_field()
    {
        $this->makeHierarchicalTaxonomy();

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->get(cp_route('taxonomies.terms.create', ['categories', 'en']).'?parent=categories::cat')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('terms/Create')
                ->where('parent', 'categories::cat')
                ->where('values.parent', ['categories::cat'])
                ->missing('parents')
            );
    }

    #[Test]
    public function creating_a_root_term_has_no_parent()
    {
        $this->makeHierarchicalTaxonomy();

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->get(cp_route('taxonomies.terms.create', ['categories', 'en']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('terms/Create')
                ->where('parent', null)
                ->where('values.parent', [])
                ->missing('parents')
            );
    }

    #[Test]
    public function creating_a_term_with_a_parent_field_grafts_it_into_the_tree()
    {
        $this->makeHierarchicalTaxonomy();

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->post(cp_route('taxonomies.terms.store', ['categories', 'en']), [
                'title' => 'Dog',
                'slug' => 'dog',
                '_blueprint' => 'category',
                'published' => true,
                'parent' => ['categories::animals'],
            ])
            ->assertOk();

        $term = Term::find('categories::dog')->inDefaultLocale();

        $this->assertEquals('animals', $term->parent()->inDefaultLocale()->slug());
        $this->assertArrayNotHasKey('parent', $term->data()->all());
        $this->assertEquals([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
                ['term' => 'dog'],
            ]],
        ], Taxonomy::findByHandle('categories')->structure()->tree()->fileData()['tree']);
    }

    #[Test]
    public function creating_a_term_without_a_parent_stays_at_the_root()
    {
        $this->makeHierarchicalTaxonomy();

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->post(cp_route('taxonomies.terms.store', ['categories', 'en']), [
                'title' => 'Dog',
                'slug' => 'dog',
                '_blueprint' => 'category',
                'published' => true,
            ])
            ->assertOk();

        $term = Term::find('categories::dog')->inDefaultLocale();

        $this->assertNull($term->parent());
        $this->assertArrayNotHasKey('parent', $term->data()->all());
        $this->assertEquals([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
            ]],
        ], Taxonomy::findByHandle('categories')->structure()->tree()->fileData()['tree']);
    }
}
