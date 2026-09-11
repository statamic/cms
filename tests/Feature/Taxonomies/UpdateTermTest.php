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
    public function it_replaces_placeholders_in_blueprint_validation_rules()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit tags terms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        Taxonomy::make('tags')->save();
        $term = tap(Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alfa')->data(['title' => 'alfa']))->save();

        $blueprint = Blueprint::makeFromFields([
            'slug' => ['type' => 'slug', 'validate' => 'new \\Statamic\\Rules\\UniqueTermValue({taxonomy}, {id}, {site})'],
        ]);

        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('in')->with('taxonomies/tags')->andReturn(collect([$blueprint]));

        $this
            ->actingAs($user)
            ->update($term, ['title' => 'Updated alfa', 'slug' => 'alfa'])
            ->assertOk();

        $term = $term->fresh();
        $this->assertEquals('Updated alfa', $term->title);
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
