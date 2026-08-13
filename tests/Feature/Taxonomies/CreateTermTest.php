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
    public function creating_a_child_term_includes_parent_breadcrumbs()
    {
        $this->makeHierarchicalTaxonomy();

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->get(cp_route('taxonomies.terms.create', ['categories', 'en']).'?parent=categories::cat')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('terms/Create')
                ->where('parent', 'categories::cat')
                ->has('parents', 2)
                ->where('parents.0.title', 'Animals')
                ->where('parents.1.title', 'Cat')
            );
    }

    #[Test]
    public function creating_a_root_term_has_no_parent_breadcrumbs()
    {
        $this->makeHierarchicalTaxonomy();

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->get(cp_route('taxonomies.terms.create', ['categories', 'en']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('terms/Create')
                ->where('parent', null)
                ->where('parents', [])
            );
    }
}
