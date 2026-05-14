<?php

namespace Tests\Feature\Taxonomies;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Tests\FakesViews;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewTermsListingTest extends TestCase
{
    use FakesViews, PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_does_not_eager_load_actions_in_listing()
    {
        $user = tap(User::make()->makeSuper())->save();

        $taxonomy = tap(Taxonomy::make('tags'))->save();
        tap(Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alfa')->data(['title' => 'alfa']))->save();

        $this
            ->actingAs($user)
            ->getJson(cp_route('taxonomies.terms.index', $taxonomy->handle()))
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonMissingPath('data.0.actions');
    }

    #[Test]
    public function it_returns_has_template_true_when_template_exists()
    {
        $this->withFakeViews();
        $this->viewShouldReturnRaw('tags.show', '');

        $user = tap(User::make()->makeSuper())->save();
        $taxonomy = tap(Taxonomy::make('tags'))->save();
        tap(Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alfa')->data(['title' => 'alfa']))->save();

        $this
            ->actingAs($user)
            ->getJson(cp_route('taxonomies.terms.index', $taxonomy->handle()))
            ->assertSuccessful()
            ->assertJsonPath('data.0.has_template', true);
    }

    #[Test]
    public function it_returns_has_template_false_when_template_does_not_exist()
    {
        $this->withFakeViews();

        $user = tap(User::make()->makeSuper())->save();
        $taxonomy = tap(Taxonomy::make('tags'))->save();
        tap(Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alfa')->data(['title' => 'alfa']))->save();

        $this
            ->actingAs($user)
            ->getJson(cp_route('taxonomies.terms.index', $taxonomy->handle()))
            ->assertSuccessful()
            ->assertJsonPath('data.0.has_template', false);
    }
}
