<?php

namespace Tests\Feature\Taxonomies;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateTermTest extends TestCase
{
    use FakesRoles, PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_denies_access_if_you_dont_have_edit_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        Taxonomy::make('tags')->save();
        $term = tap(Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alfa')->data(['title' => 'alfa']))->save();

        $this
            ->actingAs($user)
            ->update($term, ['title' => 'Updated alfa'])
            ->assertForbidden();

        $term = $term->fresh();
        $this->assertEquals('alfa', $term->title);
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_site_permission()
    {
        $this->setSites([
            'en' => ['locale' => 'en', 'url' => '/'],
            'fr' => ['locale' => 'fr', 'url' => '/fr'],
        ]);
        $this->setTestRoles(['test' => ['access cp', 'edit tags terms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        Taxonomy::make('tags')->save();
        $term = tap(Term::make()->taxonomy('tags')->slug('alfa')
            ->dataForLocale('en', ['title' => 'alfa'])
            ->dataForLocale('fr', ['title' => 'le alfa'])
        )->save();

        $term = $term->in('fr');

        $this
            ->actingAs($user)
            ->update($term, ['title' => 'Updated le alfa'])
            ->assertForbidden();

        $term = $term->fresh();
        $this->assertEquals('le alfa', $term->title);
    }

    #[Test]
    public function term_gets_updated()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit tags terms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        Taxonomy::make('tags')->save();
        $term = tap(Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alfa')->data(['title' => 'alfa']))->save();

        $this
            ->actingAs($user)
            ->update($term, ['title' => 'Updated alfa'])
            ->assertOk();

        $term = $term->fresh();
        $this->assertEquals('Updated alfa', $term->title);
    }

    #[Test]
    public function nested_term_title_can_be_updated_without_changing_slug()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit categories terms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $taxonomy = tap(Taxonomy::make('categories')->structureContents([]))->save();

        tap(Term::make('events')->taxonomy('categories')->data(['title' => 'events']))->save();
        $term = tap(Term::make('concerts')->taxonomy('categories')->data(['title' => 'concerts']))->save();

        $taxonomy->structure()->tree()->tree([
            ['term' => 'events', 'children' => [
                ['term' => 'concerts'],
            ]],
        ])->save();

        $this
            ->actingAs($user)
            ->update($term->inDefaultLocale(), ['title' => 'Concerts', 'slug' => 'concerts'])
            ->assertOk();

        $this->assertEquals('Concerts', $term->fresh()->title);
    }

    #[Test]
    public function term_title_persists_when_the_term_is_associated_in_a_site_the_taxonomy_does_not_use()
    {
        $this->setSites([
            'en' => ['locale' => 'en', 'url' => '/'],
            'de' => ['locale' => 'de', 'url' => '/de'],
        ]);
        $this->setTestRoles(['test' => ['access cp', 'edit categories terms', 'access en site']]);
        $user = tap(User::make()->assignRole('test'))->save();

        tap(Taxonomy::make('categories')->sites(['en']))->save();
        Collection::make('blog')->sites(['en', 'de'])->taxonomies(['categories'])->save();

        $term = tap(Term::make('concerts')->taxonomy('categories')->data(['title' => 'concerts']))->save();

        EntryFactory::collection('blog')
            ->locale('de')
            ->slug('show')
            ->data(['title' => 'Show', 'categories' => ['concerts']])
            ->create();

        $this
            ->actingAs($user)
            ->update($term->in('en'), ['title' => 'Concerts', 'slug' => 'concerts'])
            ->assertOk();

        $this->assertEquals('Concerts', Term::find('categories::concerts')->in('en')->title());
        $this->assertEquals('Concerts', $term->fresh()->in('en')->title());
    }

    #[Test]
    public function editing_a_term_hydrates_the_parent_field()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit categories terms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $taxonomy = tap(Taxonomy::make('categories')->structureContents([]))->save();

        tap(Term::make('animals')->taxonomy('categories')->data(['title' => 'Animals']))->save();
        $term = tap(Term::make('cat')->taxonomy('categories')->data(['title' => 'Cat']))->save();

        $taxonomy->structure()->tree()->tree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
            ]],
        ])->save();

        $this
            ->actingAs($user)
            ->get($term->inDefaultLocale()->editUrl())
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('terms/Edit')
                ->where('values.parent', ['categories::animals'])
                ->missing('parents')
            );
    }

    #[Test]
    public function a_term_can_be_reparented()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit categories terms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $taxonomy = tap(Taxonomy::make('categories')->structureContents([]))->save();

        foreach (['animals', 'cat', 'furniture'] as $slug) {
            tap(Term::make($slug)->taxonomy('categories')->data(['title' => ucfirst($slug)]))->save();
        }

        $taxonomy->structure()->tree()->tree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
            ]],
            ['term' => 'furniture'],
        ])->save();

        $term = Term::find('categories::cat')->inDefaultLocale();

        $this
            ->actingAs($user)
            ->update($term, [
                'title' => 'Cat',
                'slug' => 'cat',
                'parent' => ['categories::furniture'],
            ])
            ->assertOk();

        $term = $term->fresh()->inDefaultLocale();

        $this->assertEquals('furniture', $term->parent()->inDefaultLocale()->slug());
        $this->assertArrayNotHasKey('parent', $term->data()->all());
        $this->assertEquals([
            ['term' => 'animals'],
            ['term' => 'furniture', 'children' => [
                ['term' => 'cat'],
            ]],
        ], Taxonomy::findByHandle('categories')->structure()->tree()->fileData()['tree']);
    }

    #[Test]
    public function a_term_can_be_moved_to_the_root()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit categories terms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $taxonomy = tap(Taxonomy::make('categories')->structureContents([]))->save();

        tap(Term::make('animals')->taxonomy('categories')->data(['title' => 'Animals']))->save();
        $term = tap(Term::make('cat')->taxonomy('categories')->data(['title' => 'Cat']))->save();

        $taxonomy->structure()->tree()->tree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
            ]],
        ])->save();

        $this
            ->actingAs($user)
            ->update($term->inDefaultLocale(), [
                'title' => 'Cat',
                'slug' => 'cat',
                'parent' => [],
            ])
            ->assertOk();

        $term = $term->fresh()->inDefaultLocale();

        $this->assertNull($term->parent());
        $this->assertArrayNotHasKey('parent', $term->data()->all());
        $this->assertEquals([
            ['term' => 'animals'],
            ['term' => 'cat'],
        ], Taxonomy::findByHandle('categories')->structure()->tree()->fileData()['tree']);
    }

    #[Test]
    public function a_term_cannot_be_its_own_parent()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit categories terms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $taxonomy = tap(Taxonomy::make('categories')->structureContents([]))->save();

        tap(Term::make('animals')->taxonomy('categories')->data(['title' => 'Animals']))->save();
        $term = tap(Term::make('cat')->taxonomy('categories')->data(['title' => 'Cat']))->save();

        $taxonomy->structure()->tree()->tree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
            ]],
        ])->save();

        $this
            ->actingAs($user)
            ->update($term->inDefaultLocale(), [
                'title' => 'Cat',
                'slug' => 'cat',
                'parent' => ['categories::cat'],
            ])
            ->assertJsonValidationErrors('parent');

        $this->assertEquals('animals', $term->fresh()->inDefaultLocale()->parent()->inDefaultLocale()->slug());
    }

    #[Test]
    public function a_term_cannot_be_nested_under_a_descendant()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit categories terms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $taxonomy = tap(Taxonomy::make('categories')->structureContents([]))->save();

        foreach (['animals', 'cat'] as $slug) {
            tap(Term::make($slug)->taxonomy('categories')->data(['title' => ucfirst($slug)]))->save();
        }

        $taxonomy->structure()->tree()->tree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
            ]],
        ])->save();

        $term = Term::find('categories::animals')->inDefaultLocale();

        $this
            ->actingAs($user)
            ->update($term, [
                'title' => 'Animals',
                'slug' => 'animals',
                'parent' => ['categories::cat'],
            ])
            ->assertJsonValidationErrors('parent');

        $this->assertNull($term->fresh()->inDefaultLocale()->parent());
    }

    #[Test]
    public function a_term_cannot_be_moved_beyond_max_depth()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit categories terms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $taxonomy = tap(Taxonomy::make('categories')->structureContents(['max_depth' => 2]))->save();

        foreach (['animals', 'cat', 'furniture'] as $slug) {
            tap(Term::make($slug)->taxonomy('categories')->data(['title' => ucfirst($slug)]))->save();
        }

        $taxonomy->structure()->tree()->tree([
            ['term' => 'animals', 'children' => [
                ['term' => 'cat'],
            ]],
            ['term' => 'furniture'],
        ])->save();

        $term = Term::find('categories::furniture')->inDefaultLocale();

        $this
            ->actingAs($user)
            ->update($term, [
                'title' => 'Furniture',
                'slug' => 'furniture',
                'parent' => ['categories::cat'],
            ])
            ->assertJsonValidationErrors('parent');

        $this->assertNull($term->fresh()->inDefaultLocale()->parent());
    }

    private function update($term, $attrs = [])
    {
        $payload = array_merge([
            'title' => 'Updated term',
            'slug' => 'updated-term',
        ], $attrs);

        return $this->patchJson($term->updateUrl(), $payload);
    }
}
