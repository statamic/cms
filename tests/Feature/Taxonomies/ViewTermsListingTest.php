<?php

namespace Tests\Feature\Taxonomies;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Taxonomies\Taxonomy as TaxonomyContract;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Statamic\Http\Resources\CP\Taxonomies\Terms;
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

    #[Test]
    public function the_taxonomy_is_only_made_into_an_array_once_per_listing()
    {
        $this->app->bind(TaxonomyContract::class, CountingTaxonomy::class);
        CountingTaxonomy::$toArrayCalls = 0;

        $user = tap(User::make()->makeSuper())->save();
        $taxonomy = tap(Taxonomy::make('tags'))->save();

        foreach (['alfa', 'bravo', 'charlie'] as $slug) {
            Term::make()->taxonomy('tags')->inDefaultLocale()->slug($slug)->data(['title' => $slug])->save();
        }

        $response = $this
            ->actingAs($user)
            ->getJson(cp_route('taxonomies.terms.index', $taxonomy->handle()))
            ->assertSuccessful()
            ->assertJsonCount(3, 'data');

        $this->assertSame(1, CountingTaxonomy::$toArrayCalls);

        $expected = Taxonomy::find('tags')->toArray();

        foreach ($response->json('data') as $term) {
            $this->assertEquals($expected, $term['taxonomy']);
        }
    }

    #[Test]
    public function each_term_gets_its_own_taxonomy_when_listing_terms_from_several()
    {
        $tags = tap(Taxonomy::make('tags')->title('Tags'))->save();
        tap(Taxonomy::make('categories')->title('Categories'))->save();

        $terms = collect([
            tap(Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alfa')->data(['title' => 'Alfa']))->save(),
            tap(Term::make()->taxonomy('categories')->inDefaultLocale()->slug('bravo')->data(['title' => 'Bravo']))->save(),
            tap(Term::make()->taxonomy('tags')->inDefaultLocale()->slug('charlie')->data(['title' => 'Charlie']))->save(),
        ])->map->inDefaultLocale();

        $this->actingAs(tap(User::make()->makeSuper())->save());

        $listed = (new Terms($terms))->blueprint($tags->termBlueprint())->resolve(request());

        $this->assertSame(['tags', 'categories', 'tags'], collect($listed)->pluck('taxonomy.handle')->all());
        $this->assertSame(['Tags', 'Categories', 'Tags'], collect($listed)->pluck('taxonomy.title')->all());
    }
}

class CountingTaxonomy extends \Statamic\Taxonomies\Taxonomy
{
    public static int $toArrayCalls = 0;

    public function toArray()
    {
        static::$toArrayCalls++;

        return parent::toArray();
    }
}
