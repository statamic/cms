<?php

namespace Tests\Feature\Taxonomies;

use Facades\Statamic\Fields\BlueprintRepository;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StoreTermTest extends TestCase
{
    use FakesRoles, PreventSavingStacheItemsToDisk;

    #[Test]
    public function term_gets_created()
    {
        $this->setTestRoles(['test' => ['access cp', 'create tags terms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        Taxonomy::make('tags')->save();

        $this
            ->actingAs($user)
            ->store('tags', ['title' => 'Alfa', 'slug' => 'alfa'])
            ->assertOk();

        $this->assertEquals('Alfa', Term::find('tags::alfa')->title);
    }

    #[Test]
    public function it_replaces_placeholders_in_blueprint_validation_rules()
    {
        $this->setTestRoles(['test' => ['access cp', 'create tags terms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        Taxonomy::make('tags')->save();
        Taxonomy::make('categories')->save();
        Term::make()->taxonomy('categories')->inDefaultLocale()->slug('alfa')->data(['title' => 'alfa'])->save();

        $blueprint = Blueprint::makeFromFields([
            'slug' => ['type' => 'slug', 'validate' => 'new \\Statamic\\Rules\\UniqueTermValue({taxonomy}, {id}, {site})'],
        ]);

        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect([$blueprint]));

        $this
            ->actingAs($user)
            ->store('tags', ['title' => 'Alfa', 'slug' => 'alfa'])
            ->assertOk();

        $this->assertEquals('Alfa', Term::find('tags::alfa')->title);
    }

    /**
     * @see https://github.com/statamic/cms/issues/15951
     */
    #[Test]
    public function slug_isnt_saved_on_localization_when_term_is_created_in_another_site()
    {
        $this->setSites([
            'en' => ['locale' => 'en', 'url' => '/'],
            'fr' => ['locale' => 'fr', 'url' => '/fr'],
        ]);
        $this->setTestRoles(['test' => ['access cp', 'create tags terms', 'access fr site']]);
        $user = tap(User::make()->assignRole('test'))->save();

        Taxonomy::make('tags')->sites(['en', 'fr'])->save();

        $this
            ->actingAs($user)
            ->store('tags', ['title' => 'Le Alfa', 'slug' => 'alfa'], 'fr')
            ->assertOk();

        $term = Term::find('tags::alfa');
        $this->assertArrayNotHasKey('slug', $term->in('fr')->data()->all());

        $term->in('en')->slug('bravo')->save();

        $this->assertEquals('bravo', Term::find('tags::bravo')->in('fr')->slug());
    }

    private function store($taxonomy, $attrs = [], $site = 'en')
    {
        $payload = array_merge([
            'title' => 'New term',
            'slug' => 'new-term',
        ], $attrs);

        return $this->postJson(cp_route('taxonomies.terms.store', [$taxonomy, $site]), $payload);
    }
}
