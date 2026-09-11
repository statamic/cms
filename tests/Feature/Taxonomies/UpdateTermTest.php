<?php

namespace Tests\Feature\Taxonomies;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint;
use Tests\Fakes\FakeBlueprintRepository;
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
    public function a_blueprint_can_declare_its_own_parent_field()
    {
        BlueprintRepository::swap(new FakeBlueprintRepository(BlueprintRepository::getFacadeRoot()));

        $this->setTestRoles(['test' => ['access cp', 'edit categories terms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $taxonomy = tap(Taxonomy::make('categories')->structureContents([]))->save();

        (new Blueprint)->setHandle('category')->setNamespace('taxonomies.categories')->setContents([
            'fields' => [
                ['handle' => 'parent', 'field' => ['type' => 'text']],
            ],
        ])->save();

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
                'parent' => 'furniture',
            ])
            ->assertOk();

        $term = $term->fresh()->inDefaultLocale();

        $this->assertEquals('furniture', $term->get('parent'));
        $this->assertEquals('animals', $term->parent()->inDefaultLocale()->slug());
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
