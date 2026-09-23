<?php

namespace Tests\Feature\Entries;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\EntryCreated;
use Statamic\Events\EntryCreating;
use Statamic\Events\EntrySaved;
use Statamic\Events\EntrySaving;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EntryTitleFormatTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test')->titleFormats('{first_name} {last_name}'))->save();

        $this
            ->actingAs($user)
            ->generateForCreate($collection, ['first_name' => 'Michael'])
            ->assertForbidden();
    }

    #[Test]
    public function it_denies_access_when_editing_if_you_can_only_view_entries()
    {
        $this->setTestRoles(['test' => ['access cp', 'view test entries']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test')->titleFormats('{first_name} {last_name}'))->save();

        $entry = EntryFactory::collection($collection)
            ->slug('michael-aerni')
            ->data(['title' => 'Michael Aerni', 'first_name' => 'Michael', 'last_name' => 'Aerni'])
            ->create();

        $this
            ->actingAs($user)
            ->generateForEdit($entry, ['first_name' => 'Ruth'])
            ->assertForbidden();
    }

    #[Test]
    public function it_generates_the_title_when_creating_an_entry()
    {
        [$user, $collection] = $this->seedUserAndCollection();
        $collection->titleFormats('{first_name} {last_name}')->save();
        $this->seedBlueprintFields($collection, [
            'first_name' => ['type' => 'text'],
            'last_name' => ['type' => 'text'],
        ]);

        $this
            ->actingAs($user)
            ->generateForCreate($collection, ['first_name' => 'Michael', 'last_name' => 'Aerni'])
            ->assertOk()
            ->assertExactJson(['title' => 'Michael Aerni']);
    }

    #[Test]
    public function it_generates_the_title_when_editing_an_entry()
    {
        [$user, $collection] = $this->seedUserAndCollection();
        $collection->titleFormats('{first_name} {last_name}')->save();
        $this->seedBlueprintFields($collection, [
            'first_name' => ['type' => 'text'],
            'last_name' => ['type' => 'text'],
        ]);

        $entry = EntryFactory::collection($collection)
            ->slug('michael-aerni')
            ->data(['title' => 'Michael Aerni', 'first_name' => 'Michael', 'last_name' => 'Aerni'])
            ->create();

        $this
            ->actingAs($user)
            ->generateForEdit($entry, ['first_name' => 'Ruth', 'last_name' => 'Aerni'])
            ->assertOk()
            ->assertExactJson(['title' => 'Ruth Aerni']);
    }

    #[Test]
    public function it_generates_the_title_using_the_title_format_of_the_entrys_site()
    {
        $this->setSites([
            'en' => ['locale' => 'en', 'url' => '/'],
            'fr' => ['locale' => 'fr', 'url' => '/fr/'],
        ]);

        [$user, $collection] = $this->seedUserAndCollection();
        $collection->sites(['en', 'fr'])->titleFormats([
            'en' => '{first_name} {last_name}',
            'fr' => '{last_name}, {first_name}',
        ])->save();
        $this->seedBlueprintFields($collection, [
            'first_name' => ['type' => 'text'],
            'last_name' => ['type' => 'text'],
        ]);

        $entry = EntryFactory::collection($collection)
            ->locale('fr')
            ->slug('michael-aerni')
            ->data(['title' => 'Michael Aerni', 'first_name' => 'Michael', 'last_name' => 'Aerni'])
            ->create();

        $this
            ->actingAs($user)
            ->generateForEdit($entry, ['first_name' => 'Ruth', 'last_name' => 'Aerni'])
            ->assertOk()
            ->assertExactJson(['title' => 'Aerni, Ruth']);
    }

    #[Test]
    public function it_generates_the_title_using_the_submitted_date()
    {
        [$user, $collection] = $this->seedUserAndCollection();
        $collection->dated(true)->titleFormats('{{ first_name }} {{ last_name }} ({{ date format="Y" }})')->save();
        $this->seedBlueprintFields($collection, [
            'first_name' => ['type' => 'text'],
            'last_name' => ['type' => 'text'],
        ]);

        $this
            ->actingAs($user)
            ->generateForCreate($collection, ['first_name' => 'Michael', 'last_name' => 'Aerni', 'date' => '2023-01-18'])
            ->assertOk()
            ->assertExactJson(['title' => 'Michael Aerni (2023)']);
    }

    #[Test]
    public function it_ignores_the_date_when_it_hasnt_been_submitted()
    {
        [$user, $collection] = $this->seedUserAndCollection();
        $collection->dated(true)->titleFormats('{{ first_name }} ({{ date format="Y" }})')->save();
        $this->seedBlueprintFields($collection, ['first_name' => ['type' => 'text']]);

        $entry = EntryFactory::collection($collection)
            ->slug('michael')
            ->date('2023-01-18')
            ->data(['title' => 'Michael (2023)', 'first_name' => 'Michael'])
            ->create();

        $this
            ->actingAs($user)
            ->generateForEdit($entry, ['first_name' => 'Ruth'])
            ->assertOk()
            ->assertExactJson(['title' => 'Ruth (2023)']);
    }

    #[Test]
    public function it_generates_the_title_using_the_submitted_blueprint()
    {
        [$user, $collection] = $this->seedUserAndCollection();
        $collection->titleFormats('{first_name} {last_name}')->save();

        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('in')
            ->with('collections/test')
            ->andReturn(collect([
                'first' => Blueprint::makeFromFields(['first_name' => ['type' => 'text']])->setHandle('first'),
                'second' => Blueprint::makeFromFields([
                    'first_name' => ['type' => 'text'],
                    'last_name' => ['type' => 'text'],
                ])->setHandle('second'),
            ]));

        $entry = EntryFactory::collection($collection)
            ->blueprint('first')
            ->slug('michael')
            ->data(['title' => 'Michael', 'first_name' => 'Michael'])
            ->create();

        $this
            ->actingAs($user)
            ->generateForEdit($entry, ['first_name' => 'Ruth', 'last_name' => 'Aerni'], 'second')
            ->assertOk()
            ->assertExactJson(['title' => 'Ruth Aerni']);
    }

    #[Test]
    public function it_generates_the_title_from_the_working_copy()
    {
        config(['statamic.revisions.enabled' => true]);

        [$user, $collection] = $this->seedUserAndCollection();
        $collection->revisionsEnabled(true)->titleFormats('{first_name} {last_name}')->save();
        $this->seedBlueprintFields($collection, [
            'first_name' => ['type' => 'text'],
            'last_name' => ['type' => 'text'],
        ]);

        $entry = EntryFactory::collection($collection)
            ->slug('michael-aerni')
            ->data(['title' => 'Michael Aerni', 'first_name' => 'Michael', 'last_name' => 'Aerni'])
            ->create();

        tap($entry->makeWorkingCopy(), function ($copy) {
            $attrs = $copy->attributes();
            $attrs['data']['last_name'] = 'Muster';
            $copy->attributes($attrs);
        })->save();

        $this
            ->actingAs($user)
            ->generateForEdit($entry, ['first_name' => 'Ruth'])
            ->assertOk()
            ->assertExactJson(['title' => 'Ruth Muster']);
    }

    #[Test]
    public function it_never_persists_anything()
    {
        [$user, $collection] = $this->seedUserAndCollection();
        $collection->titleFormats('{first_name} {last_name}')->save();
        $this->seedBlueprintFields($collection, [
            'first_name' => ['type' => 'text'],
            'last_name' => ['type' => 'text'],
        ]);

        $entry = EntryFactory::collection($collection)
            ->slug('michael-aerni')
            ->data(['title' => 'Michael Aerni', 'first_name' => 'Michael', 'last_name' => 'Aerni'])
            ->create();

        Event::fake();

        $this
            ->actingAs($user)
            ->generateForCreate($collection, ['first_name' => 'Ruth', 'last_name' => 'Muster'])
            ->assertOk();

        $this
            ->actingAs($user)
            ->generateForEdit($entry, ['first_name' => 'Ruth', 'last_name' => 'Muster'])
            ->assertOk();

        Event::assertNotDispatched(EntryCreating::class);
        Event::assertNotDispatched(EntryCreated::class);
        Event::assertNotDispatched(EntrySaving::class);
        Event::assertNotDispatched(EntrySaved::class);

        $this->assertCount(1, Entry::all());
        $this->assertEquals('Michael Aerni', $entry->fresh()->value('title'));
    }

    #[Test]
    public function the_edit_form_gets_the_endpoint_and_the_fields_the_format_references()
    {
        [$user, $collection] = $this->seedUserAndCollection();
        $collection->titleFormats('{first_name} {last_name}')->save();

        $entry = EntryFactory::collection($collection)
            ->slug('michael-aerni')
            ->data(['title' => 'Michael Aerni', 'first_name' => 'Michael', 'last_name' => 'Aerni'])
            ->create();

        $this
            ->actingAs($user)
            ->getJson($entry->editUrl())
            ->assertOk()
            ->assertJsonPath('titleFormat.url', cp_route('collections.entries.title-format.edit', ['test', $entry->id()]))
            ->assertJsonPath('titleFormat.fields', ['first_name', 'last_name']);
    }

    #[Test]
    public function the_edit_form_doesnt_get_the_endpoint_if_you_can_only_view_entries()
    {
        $this->setTestRoles(['test' => ['access cp', 'view test entries']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test')->titleFormats('{first_name} {last_name}'))->save();

        $entry = EntryFactory::collection($collection)
            ->slug('michael-aerni')
            ->data(['title' => 'Michael Aerni', 'first_name' => 'Michael', 'last_name' => 'Aerni'])
            ->create();

        $this
            ->actingAs($user)
            ->getJson($entry->editUrl())
            ->assertOk()
            ->assertJsonPath('titleFormat', null);
    }

    #[Test]
    public function it_denies_access_when_the_collection_isnt_available_on_the_site()
    {
        $this->setSites([
            'en' => ['locale' => 'en', 'url' => '/'],
            'fr' => ['locale' => 'fr', 'url' => '/fr/'],
        ]);

        [$user, $collection] = $this->seedUserAndCollection();
        $collection->sites(['fr'])->titleFormats('{first_name} {last_name}')->save();

        $this
            ->actingAs($user)
            ->generateForCreate($collection, ['first_name' => 'Michael'])
            ->assertForbidden();
    }

    #[Test]
    public function it_404s_when_the_collection_doesnt_generate_titles()
    {
        [$user, $collection] = $this->seedUserAndCollection();

        $this
            ->actingAs($user)
            ->generateForCreate($collection, ['first_name' => 'Michael'])
            ->assertNotFound();
    }

    private function seedUserAndCollection()
    {
        $this->setTestRoles(['test' => [
            'access cp',
            'create test entries',
            'edit test entries',
            'access en site',
            'access fr site',
        ]]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test'))->save();

        return [$user, $collection];
    }

    private function seedBlueprintFields($collection, $fields)
    {
        $blueprint = Blueprint::makeFromFields($fields);

        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('in')
            ->with('collections/'.$collection->handle())
            ->andReturn(collect([$blueprint]));
    }

    private function generateForCreate($collection, $values)
    {
        return $this->postJson(
            cp_route('collections.entries.title-format.create', [$collection->handle(), 'en']),
            ['values' => $values]
        );
    }

    private function generateForEdit($entry, $values, $blueprint = null)
    {
        return $this->postJson(
            cp_route('collections.entries.title-format.edit', [$entry->collectionHandle(), $entry->id()]),
            ['blueprint' => $blueprint, 'values' => $values]
        );
    }
}
