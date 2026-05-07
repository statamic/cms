<?php

namespace Tests\Feature\Taxonomies;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ViewTermsListingTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

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
}
