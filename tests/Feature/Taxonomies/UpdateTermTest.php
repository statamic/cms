<?php

namespace Tests\Feature\Taxonomies;

use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\TermSaving;
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
    public function default_values_are_returned_for_fields_saved_empty()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit tags terms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        Taxonomy::make('tags')->save();
        $this->seedBlueprintFields('tags', [
            'nutrition_table' => [
                'type' => 'grid',
                'default' => [['name' => 'Sugar'], ['name' => 'Salt']],
                'fields' => [['handle' => 'name', 'field' => ['type' => 'text']]],
            ],
        ]);
        $term = tap(Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alfa')->data(['title' => 'alfa']))->save();

        $response = $this
            ->actingAs($user)
            ->update($term, ['title' => 'Updated alfa'])
            ->assertOk();

        $this->assertEquals(['Sugar', 'Salt'], collect($response->json('data.values.nutrition_table'))->pluck('name')->all());
    }

    #[Test]
    public function meta_reflects_values_changed_while_saving()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit tags terms', 'view topics terms']]);
        $user = tap(User::make()->assignRole('test'))->save();

        Taxonomy::make('tags')->save();
        Taxonomy::make('topics')->save();
        $this->seedBlueprintFields('tags', [
            'topics' => ['type' => 'terms', 'taxonomies' => ['topics']],
        ]);
        $this->seedBlueprintFields('topics', []);
        Term::make()->taxonomy('topics')->inDefaultLocale()->slug('bravo')->data(['title' => 'Bravo'])->save();
        $term = tap(Term::make()->taxonomy('tags')->inDefaultLocale()->slug('alfa')->data(['title' => 'alfa']))->save();

        Event::listen(TermSaving::class, function (TermSaving $event) {
            $event->term->set('topics', ['bravo']);
        });

        $response = $this
            ->actingAs($user)
            ->update($term, ['title' => 'Updated alfa'])
            ->assertOk();

        $this->assertEquals(['topics::bravo'], $response->json('data.values.topics'));
        $this->assertEquals('Bravo', $response->json('data.meta.topics.data.0.title'));
    }

    private function seedBlueprintFields($taxonomy, $fields)
    {
        $blueprint = Blueprint::makeFromFields($fields);

        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('in')
            ->with('taxonomies/'.$taxonomy)
            ->andReturn(collect([$blueprint]));
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
