<?php

namespace Tests\Feature\Entries;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\EntrySaving;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EntrySlugFormatTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test')->slugFormats('{issue}-{title}'))->save();

        $this
            ->actingAs($user)
            ->generateForCreate($collection, ['issue' => 56])
            ->assertForbidden();
    }

    #[Test]
    public function it_denies_access_when_editing_if_you_can_only_view_entries()
    {
        $this->setTestRoles(['test' => ['access cp', 'view test entries']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test')->slugFormats('{issue}-{title}'))->save();

        $entry = EntryFactory::collection($collection)
            ->slug('56-summer-2026')
            ->data(['title' => 'Summer 2026', 'issue' => 56])
            ->create();

        $this
            ->actingAs($user)
            ->generateForEdit($entry, ['issue' => 57])
            ->assertForbidden();
    }

    #[Test]
    public function it_generates_the_slug_when_creating_an_entry()
    {
        [$user, $collection] = $this->seedUserAndCollection();
        $collection->slugFormats('{issue}-{title}')->save();
        $this->seedBlueprintFields($collection, ['issue' => ['type' => 'integer']]);

        $this
            ->actingAs($user)
            ->generateForCreate($collection, ['title' => 'Summer 2026', 'issue' => 56])
            ->assertOk()
            ->assertExactJson(['slug' => '56-summer-2026']);
    }

    #[Test]
    public function it_generates_the_slug_when_editing_an_entry()
    {
        [$user, $collection] = $this->seedUserAndCollection();
        $collection->slugFormats('{issue}-{title}')->save();
        $this->seedBlueprintFields($collection, ['issue' => ['type' => 'integer']]);

        $entry = EntryFactory::collection($collection)
            ->slug('56-summer-2026')
            ->data(['title' => 'Summer 2026', 'issue' => 56])
            ->create();

        $this
            ->actingAs($user)
            ->generateForEdit($entry, ['title' => 'Winter 2026'])
            ->assertOk()
            ->assertExactJson(['slug' => '56-winter-2026']);
    }

    #[Test]
    public function it_generates_the_slug_using_the_title_format()
    {
        [$user, $collection] = $this->seedUserAndCollection();
        $collection->titleFormats('{season} {year}')->slugFormats('{issue}-{title}')->save();
        $this->seedBlueprintFields($collection, [
            'issue' => ['type' => 'integer'],
            'season' => ['type' => 'text'],
            'year' => ['type' => 'integer'],
        ]);

        $this
            ->actingAs($user)
            ->generateForCreate($collection, ['title' => 'Stale', 'issue' => 56, 'season' => 'Summer', 'year' => 2026])
            ->assertOk()
            ->assertExactJson(['slug' => '56-summer-2026']);
    }

    #[Test]
    public function it_generates_the_slug_using_the_slug_format_of_the_entrys_site()
    {
        $this->setSites([
            'en' => ['locale' => 'en', 'url' => '/'],
            'fr' => ['locale' => 'fr', 'url' => '/fr/'],
        ]);

        [$user, $collection] = $this->seedUserAndCollection();
        $collection->sites(['en', 'fr'])->slugFormats([
            'en' => '{issue}-{title}',
            'fr' => '{title}-{issue}',
        ])->save();
        $this->seedBlueprintFields($collection, ['issue' => ['type' => 'integer']]);

        $entry = EntryFactory::collection($collection)
            ->locale('fr')
            ->slug('ete-2026-56')
            ->data(['title' => 'Ete 2026', 'issue' => 56])
            ->create();

        $this
            ->actingAs($user)
            ->generateForEdit($entry, ['issue' => 57])
            ->assertOk()
            ->assertExactJson(['slug' => 'ete-2026-57']);
    }

    #[Test]
    public function it_never_persists_anything()
    {
        [$user, $collection] = $this->seedUserAndCollection();
        $collection->slugFormats('{issue}-{title}')->save();
        $this->seedBlueprintFields($collection, ['issue' => ['type' => 'integer']]);

        $entry = EntryFactory::collection($collection)
            ->slug('56-summer-2026')
            ->data(['title' => 'Summer 2026', 'issue' => 56])
            ->create();

        Event::fake();

        $this
            ->actingAs($user)
            ->generateForCreate($collection, ['title' => 'Winter 2026', 'issue' => 57])
            ->assertOk();

        $this
            ->actingAs($user)
            ->generateForEdit($entry, ['issue' => 57])
            ->assertOk();

        Event::assertNotDispatched(EntrySaving::class);

        $this->assertCount(1, Entry::all());
        $this->assertEquals('56-summer-2026', $entry->fresh()->slug());
    }

    #[Test]
    public function the_edit_form_gets_the_endpoint_and_the_fields_the_format_references()
    {
        [$user, $collection] = $this->seedUserAndCollection();
        $collection->slugFormats('{issue}-{title}')->save();

        $entry = EntryFactory::collection($collection)
            ->slug('56-summer-2026')
            ->data(['title' => 'Summer 2026', 'issue' => 56])
            ->create();

        $this
            ->actingAs($user)
            ->getJson($entry->editUrl())
            ->assertOk()
            ->assertJsonPath('slugFormat.url', cp_route('collections.entries.slug-format.edit', ['test', $entry->id()]))
            ->assertJsonPath('slugFormat.fields', ['issue', 'title']);
    }

    #[Test]
    public function the_edit_form_doesnt_get_the_endpoint_if_you_can_only_view_entries()
    {
        $this->setTestRoles(['test' => ['access cp', 'view test entries']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test')->slugFormats('{issue}-{title}'))->save();

        $entry = EntryFactory::collection($collection)
            ->slug('56-summer-2026')
            ->data(['title' => 'Summer 2026', 'issue' => 56])
            ->create();

        $this
            ->actingAs($user)
            ->getJson($entry->editUrl())
            ->assertOk()
            ->assertJsonPath('slugFormat', null);
    }

    #[Test]
    public function the_fields_include_those_of_the_title_format()
    {
        [$user, $collection] = $this->seedUserAndCollection();
        $collection->titleFormats('{season} {year}')->slugFormats('{issue}-{title}')->save();

        $entry = EntryFactory::collection($collection)
            ->slug('56-summer-2026')
            ->data(['issue' => 56, 'season' => 'Summer', 'year' => 2026])
            ->create();

        $this
            ->actingAs($user)
            ->getJson($entry->editUrl())
            ->assertOk()
            ->assertJsonPath('slugFormat.fields', ['issue', 'season', 'year']);
    }

    #[Test]
    public function it_404s_when_the_collection_doesnt_generate_slugs()
    {
        [$user, $collection] = $this->seedUserAndCollection();

        $this
            ->actingAs($user)
            ->generateForCreate($collection, ['issue' => 56])
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
            cp_route('collections.entries.slug-format.create', [$collection->handle(), 'en']),
            ['values' => $values]
        );
    }

    private function generateForEdit($entry, $values, $blueprint = null)
    {
        return $this->postJson(
            cp_route('collections.entries.slug-format.edit', [$entry->collectionHandle(), $entry->id()]),
            ['blueprint' => $blueprint, 'values' => $values]
        );
    }
}
