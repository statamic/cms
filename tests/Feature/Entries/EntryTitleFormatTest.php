<?php

namespace Tests\Feature\Entries;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
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

    private function generateForEdit($entry, $values)
    {
        return $this->postJson(
            cp_route('collections.entries.title-format.edit', [$entry->collectionHandle(), $entry->id()]),
            ['values' => $values]
        );
    }
}
