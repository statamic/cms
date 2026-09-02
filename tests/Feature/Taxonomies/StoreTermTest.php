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

    private function store($taxonomy, $attrs = [])
    {
        $payload = array_merge([
            'title' => 'New term',
            'slug' => 'new-term',
        ], $attrs);

        return $this->postJson(cp_route('taxonomies.terms.store', [$taxonomy, 'en']), $payload);
    }
}
