<?php

namespace Tests\Feature\Taxonomies;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Statamic\Structures\TaxonomyStructure;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ReorderTermsTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private $structure;
    private $taxonomy;

    public function setUp(): void
    {
        parent::setUp();

        $this->structure = (new TaxonomyStructure)->maxDepth(1);

        $this->taxonomy = Taxonomy::make('test')
            ->sites(['en'])
            ->structure($this->structure)
            ->save();

        $this->structure->tree()->save();
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->reorder([])
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_denies_access_if_the_taxonomy_is_not_orderable()
    {
        $this->setTestRoles(['test' => ['access cp', 'reorder test terms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        Taxonomy::make('test')->sites(['en'])->save();

        $this
            ->from('/original')
            ->actingAs($user)
            ->reorder([])
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_reorders_terms()
    {
        tap(Term::make('one')->taxonomy('test')->data(['title' => 'One']))->save();
        tap(Term::make('two')->taxonomy('test')->data(['title' => 'Two']))->save();
        tap(Term::make('three')->taxonomy('test')->data(['title' => 'Three']))->save();

        $this->structure->tree()->tree([
            ['term' => 'one'],
            ['term' => 'two'],
            ['term' => 'three'],
        ])->save();

        $this->setTestRoles(['test' => ['access cp', 'reorder test terms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->reorder(['page' => 1, 'perPage' => 3, 'ids' => ['test::three', 'test::one', 'test::two']])
            ->assertOk();

        $this->assertEquals([
            ['term' => 'three'],
            ['term' => 'one'],
            ['term' => 'two'],
        ], $this->structure->tree()->tree());
        $this->assertEquals(2, Term::find('test::one')->order());
        $this->assertEquals(3, Term::find('test::two')->order());
        $this->assertEquals(1, Term::find('test::three')->order());
    }

    #[Test]
    public function it_reorders_paginated_terms()
    {
        foreach (['one', 'two', 'three', 'four', 'five', 'six', 'seven'] as $slug) {
            tap(Term::make($slug)->taxonomy('test')->data(['title' => ucfirst($slug)]))->save();
        }

        $this->structure->tree()->tree([
            ['term' => 'one'],
            ['term' => 'two'],
            ['term' => 'three'],
            ['term' => 'four'],
            ['term' => 'five'],
            ['term' => 'six'],
            ['term' => 'seven'],
        ])->save();

        $this->setTestRoles(['test' => ['access cp', 'reorder test terms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        $this
            ->actingAs($user)
            ->reorder(['page' => 2, 'perPage' => 3, 'ids' => ['test::six', 'test::four', 'test::five']])
            ->assertOk();

        $this->assertEquals([
            ['term' => 'one'],
            ['term' => 'two'],
            ['term' => 'three'],
            ['term' => 'six'],
            ['term' => 'four'],
            ['term' => 'five'],
            ['term' => 'seven'],
        ], $this->structure->tree()->tree());
        $this->assertEquals(1, Term::find('test::one')->order());
        $this->assertEquals(2, Term::find('test::two')->order());
        $this->assertEquals(3, Term::find('test::three')->order());
        $this->assertEquals(4, Term::find('test::six')->order());
        $this->assertEquals(5, Term::find('test::four')->order());
        $this->assertEquals(6, Term::find('test::five')->order());
        $this->assertEquals(7, Term::find('test::seven')->order());
    }

    private function reorder($payload)
    {
        return $this->post(cp_route('taxonomies.terms.reorder', 'test'), array_merge(['site' => 'en'], $payload));
    }
}
