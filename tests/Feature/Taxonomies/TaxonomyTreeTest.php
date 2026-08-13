<?php

namespace Tests\Feature\Taxonomies;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TaxonomyTreeTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private function makeStructuredTaxonomy()
    {
        $taxonomy = tap(Taxonomy::make('categories')->title('Categories')->structureContents([]))->save();

        foreach (['animals', 'cat', 'furniture'] as $slug) {
            tap(Term::make($slug)->taxonomy('categories')->data(['title' => ucfirst($slug)]))->save();
        }

        $taxonomy->structure()->tree()->tree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
            ]],
            ['term' => 'furniture'],
        ])->save();

        return $taxonomy;
    }

    #[Test]
    public function it_gets_the_tree()
    {
        $taxonomy = $this->makeStructuredTaxonomy();
        $user = tap(User::make()->makeSuper())->save();

        $pages = $this
            ->actingAs($user)
            ->get(cp_route('taxonomies.tree.index', 'categories'))
            ->assertOk()
            ->json('pages');

        $this->assertEquals('categories::animals', $pages[0]['id']);
        $this->assertEquals('Animals', $pages[0]['entry_title']);
        $this->assertEquals('categories::cat', $pages[0]['children'][0]['id']);
        $this->assertEquals('categories::furniture', $pages[1]['id']);
    }

    #[Test]
    public function it_gets_the_tree_when_branches_use_entry_keys_and_full_ids()
    {
        $taxonomy = $this->makeStructuredTaxonomy();

        $taxonomy->structure()->tree()->tree([
            ['entry' => 'categories::animals', 'children' => [
                ['entry' => 'categories::cat'],
            ]],
            ['term' => 'furniture'],
            ['term' => 'animals'],
            ['term' => 'cat'],
        ])->save();

        $user = tap(User::make()->makeSuper())->save();

        $pages = $this
            ->actingAs($user)
            ->get(cp_route('taxonomies.tree.index', 'categories'))
            ->assertOk()
            ->json('pages');

        $this->assertCount(2, $pages);
        $this->assertEquals('categories::animals', $pages[0]['id']);
        $this->assertEquals('categories::cat', $pages[0]['children'][0]['id']);
        $this->assertEquals('categories::furniture', $pages[1]['id']);
    }

    #[Test]
    public function it_updates_the_tree()
    {
        $taxonomy = $this->makeStructuredTaxonomy();
        $user = tap(User::make()->makeSuper())->save();

        $this
            ->actingAs($user)
            ->patch(cp_route('taxonomies.tree.update', 'categories'), [
                'pages' => [
                    ['id' => 'categories::furniture', 'children' => [
                        ['id' => 'categories::animals', 'children' => [
                            ['id' => 'categories::cat', 'children' => []],
                        ]],
                    ]],
                ],
            ])
            ->assertOk();

        $this->assertEquals([
            ['term' => 'furniture', 'children' => [
                ['term' => 'animals', 'children' => [
                    ['term' => 'cat'],
                ]],
            ]],
        ], $taxonomy->structure()->tree()->tree());
    }

    #[Test]
    public function it_grafts_a_child_term_into_a_tree_that_still_uses_entry_keys()
    {
        $taxonomy = $this->makeStructuredTaxonomy();

        $taxonomy->structure()->tree()->tree([
            ['entry' => 'categories::animals', 'children' => [
                ['entry' => 'categories::cat'],
            ]],
            ['term' => 'furniture'],
        ])->save();

        $user = tap(User::make()->makeSuper())->save();

        $this
            ->actingAs($user)
            ->post(cp_route('taxonomies.terms.store', ['categories', 'en']), [
                'title' => 'Dog',
                'slug' => 'dog',
                '_blueprint' => 'category',
                'published' => true,
                '_parent' => 'categories::animals',
            ])
            ->assertOk();

        $this->assertEquals([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
                ['term' => 'dog'],
            ]],
            ['term' => 'furniture'],
        ], Taxonomy::findByHandle('categories')->structure()->tree()->fileData()['tree']);
    }

    #[Test]
    public function it_deletes_a_term_and_promotes_its_children()
    {
        $taxonomy = $this->makeStructuredTaxonomy();
        $user = tap(User::make()->makeSuper())->save();

        $this
            ->actingAs($user)
            ->patch(cp_route('taxonomies.tree.update', 'categories'), [
                'deletedTerms' => ['categories::animals'],
                'pages' => [
                    ['id' => 'categories::cat', 'children' => []],
                    ['id' => 'categories::furniture', 'children' => []],
                ],
            ])
            ->assertOk();

        $this->assertNull(Term::find('categories::animals'));
        $this->assertNotNull(Term::find('categories::cat'));
        $this->assertEquals([
            ['term' => 'cat'],
            ['term' => 'furniture'],
        ], Taxonomy::findByHandle('categories')->structure()->tree()->tree());
    }

    #[Test]
    public function it_deletes_a_term_and_its_children()
    {
        $taxonomy = $this->makeStructuredTaxonomy();
        $user = tap(User::make()->makeSuper())->save();

        $this
            ->actingAs($user)
            ->patch(cp_route('taxonomies.tree.update', 'categories'), [
                'deletedTerms' => ['categories::animals', 'categories::cat'],
                'pages' => [
                    ['id' => 'categories::furniture', 'children' => []],
                ],
            ])
            ->assertOk();

        $this->assertNull(Term::find('categories::animals'));
        $this->assertNull(Term::find('categories::cat'));
        $this->assertNotNull(Term::find('categories::furniture'));
        $this->assertEquals([
            ['term' => 'furniture'],
        ], Taxonomy::findByHandle('categories')->structure()->tree()->tree());
    }

    #[Test]
    public function deleting_a_term_from_the_tree_removes_it_from_entries()
    {
        $this->makeStructuredTaxonomy();
        tap(Collection::make('articles')->taxonomies(['categories']))->save();

        $entry = tap(Entry::make()->collection('articles')->data([
            'title' => 'Show',
            'categories' => ['animals', 'furniture'],
        ]))->save();

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->patch(cp_route('taxonomies.tree.update', 'categories'), [
                'deletedTerms' => ['categories::animals'],
                'pages' => [
                    ['id' => 'categories::cat', 'children' => []],
                    ['id' => 'categories::furniture', 'children' => []],
                ],
            ])
            ->assertOk();

        $this->assertEquals(['furniture'], $entry->fresh()->get('categories'));
    }

    #[Test]
    public function the_tree_is_visible_without_reorder_permission()
    {
        $this->makeStructuredTaxonomy();
        $this->setTestRoles(['test' => ['access cp', 'view categories terms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('taxonomies.show', 'categories'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('taxonomies/Show')
                ->where('structured', true)
                ->where('canReorder', false)
            );
    }

    #[Test]
    public function the_tree_is_editable_with_reorder_permission()
    {
        $this->makeStructuredTaxonomy();
        $this->setTestRoles(['test' => ['access cp', 'view categories terms', 'reorder categories terms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->get(cp_route('taxonomies.show', 'categories'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('taxonomies/Show')
                ->where('structured', true)
                ->where('canReorder', true)
            );
    }

    #[Test]
    public function it_rejects_a_tree_deeper_than_max_depth()
    {
        $taxonomy = tap(Taxonomy::make('categories')->title('Categories')->structureContents(['max_depth' => 2]))->save();

        foreach (['animals', 'cat', 'calico'] as $slug) {
            tap(Term::make($slug)->taxonomy('categories')->data(['title' => ucfirst($slug)]))->save();
        }

        $taxonomy->structure()->tree()->tree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
            ]],
        ])->save();

        $this
            ->actingAs(tap(User::make()->makeSuper())->save())
            ->patchJson(cp_route('taxonomies.tree.update', 'categories'), [
                'pages' => [
                    ['id' => 'categories::animals', 'children' => [
                        ['id' => 'categories::cat', 'children' => [
                            ['id' => 'categories::calico', 'children' => []],
                        ]],
                    ]],
                ],
            ])
            ->assertUnprocessable();
    }
}
