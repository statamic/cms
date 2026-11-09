<?php

namespace Tests\Feature\Entries;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Actions\Action;
use Statamic\Facades\Blink;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EntryVersionConflictTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->setTestRoles(['test' => ['access cp', 'edit test entries', 'publish test entries']]);
        $this->user = tap(User::make()->assignRole('test')->set('name', 'Jane Doe'))->save();
    }

    #[Test]
    public function it_keeps_saving_with_the_version_returned_by_the_previous_save()
    {
        $entry = $this->createEntry();

        $first = $this->save($entry, 'First', $this->loadVersion($entry))->assertOk();
        $this->save($entry, 'Second', $first->json('data.values._version'))->assertOk();

        $this->assertEquals('Second', Entry::find($entry->id())->value('title'));
    }

    #[Test]
    public function it_rejects_a_save_based_on_an_outdated_version()
    {
        $entry = $this->createEntry();
        $staleVersion = $this->loadVersion($entry);

        $this->save($entry, 'Saved by someone else', $this->loadVersion($entry))->assertOk();

        $this->save($entry, 'Stale overwrite', $staleVersion)
            ->assertStatus(409)
            ->assertJsonPath('version', $this->loadVersion($entry));

        $this->assertEquals('Saved by someone else', Entry::find($entry->id())->value('title'));
    }

    #[Test]
    public function it_saves_over_the_changes_with_the_version_returned_by_the_conflict()
    {
        $entry = $this->createEntry();
        $staleVersion = $this->loadVersion($entry);
        $this->save($entry, 'Saved by someone else', $this->loadVersion($entry))->assertOk();

        $conflict = $this->save($entry, 'Saved anyway', $staleVersion)->assertStatus(409);
        $this->save($entry, 'Saved anyway', $conflict->json('version'))->assertOk();

        $this->assertEquals('Saved anyway', Entry::find($entry->id())->value('title'));
    }

    #[Test]
    public function it_rejects_a_save_when_the_entry_was_changed_outside_the_control_panel()
    {
        $entry = $this->createEntry();
        $version = $this->loadVersion($entry);

        Entry::find($entry->id())->set('title', 'Changed by an import')->save();

        $this->save($entry, 'Stale overwrite', $version)->assertStatus(409);
    }

    #[Test]
    public function it_keeps_the_version_when_the_entry_is_reloaded_from_disk()
    {
        $blueprint = Blueprint::makeFromFields([
            'title' => ['type' => 'text'],
            'empty_text' => ['type' => 'text'],
            'empty_list' => ['type' => 'list'],
        ])->setHandle('test');
        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('in')->with('collections/test')->andReturn(collect(['test' => $blueprint]));

        $entry = $this->createEntry();

        $saved = $this->actingAs($this->user)->patchJson($entry->updateUrl(), [
            'title' => 'Saved',
            'slug' => 'existing-entry',
            'empty_text' => '',
            'empty_list' => [],
            '_version' => $this->loadVersion($entry),
        ])->assertOk();

        Stache::store('entries')->store('test')->forgetItem($entry->id());
        Blink::flush();

        $this->save($entry, 'Saved again', $saved->json('data.values._version'))->assertOk();
    }

    #[Test]
    public function it_detects_an_explicitly_blank_localized_field()
    {
        config(['statamic.revisions.enabled' => true]);
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US', 'name' => 'English'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr_FR', 'name' => 'French'],
        ]);
        $this->setTestRoles(['test' => ['access cp', 'edit test entries', 'publish test entries', 'access en site', 'access fr site']]);
        $collection = tap(Collection::make('test')->sites(['en', 'fr'])->revisionsEnabled(true))->save();
        EntryFactory::collection($collection)->id('origin')->locale('en')->slug('a')->data(['title' => 'A', 'foo' => 'inherited'])->create();
        $localization = EntryFactory::collection($collection)->id('localization')->locale('fr')->origin('origin')->slug('a')->data(['title' => 'A fr'])->create();
        $localization->makeWorkingCopy()->save();

        $staleVersion = $this->loadVersion($localization);

        $workingCopy = Entry::find('localization')->workingCopy();
        $attributes = $workingCopy->attributes();
        $attributes['data']['foo'] = null;
        $workingCopy->attributes($attributes)->save();

        $this->actingAs($this->user)->patchJson($localization->updateUrl(), [
            'title' => 'Stale overwrite',
            'slug' => 'a',
            '_localized' => ['title'],
            '_version' => $staleVersion,
        ])->assertStatus(409);
    }

    #[Test]
    public function it_returns_the_working_copy_with_the_version_after_a_form_action()
    {
        config(['statamic.revisions.enabled' => true]);
        $entry = $this->createEntry(revisions: true);
        $this->save($entry, 'Draft by someone else', $this->loadVersion($entry))->assertOk();
        StayOnFormAction::register();

        $response = $this->actingAs($this->user)->postJson(cp_route('collections.entries.actions.run', 'test'), [
            'action' => 'stay_on_form',
            'selections' => [$entry->id()],
            'context' => ['view' => 'form'],
            'values' => [],
        ])->assertOk();

        // The form replaces its values with these, so they must match the state the version describes.
        $this->assertEquals('Draft by someone else', $response->json('data.values.title'));
        $this->save($entry, 'Draft by someone else, edited', $response->json('data.values._version'))->assertOk();
    }

    #[Test]
    public function it_does_not_reject_a_localization_when_only_its_origin_changed()
    {
        $localization = $this->createLocalization(['title' => 'Same']);
        $version = $this->loadVersion($localization);

        Entry::find('origin')->set('title', 'Changed')->date('2025-06-01')->save();

        $this->saveLocalization($localization, 'Same', $version)->assertOk();
    }

    #[Test]
    public function it_detects_a_localized_value_equal_to_the_origin()
    {
        $localization = $this->createLocalization([]);
        $staleVersion = $this->loadVersion($localization);

        Entry::find('localization')->set('title', 'Same')->save();

        $this->saveLocalization($localization, 'Stale overwrite', $staleVersion)->assertStatus(409);
    }

    #[Test]
    public function it_does_not_check_saves_without_a_version()
    {
        $entry = $this->createEntry();

        $this->save($entry, 'Updated', null)->assertOk();

        $this->assertEquals('Updated', Entry::find($entry->id())->value('title'));
    }

    #[Test]
    public function it_checks_the_working_copy_when_revisions_are_enabled()
    {
        config(['statamic.revisions.enabled' => true]);
        $entry = $this->createEntry(revisions: true);
        $staleVersion = $this->loadVersion($entry);

        $saved = $this->save($entry, 'Draft by someone else', $this->loadVersion($entry))->assertOk();
        $this->assertTrue(Entry::find($entry->id())->hasWorkingCopy());

        $this->save($entry, 'Stale overwrite', $staleVersion)->assertStatus(409);
        $this->save($entry, 'Second draft', $saved->json('data.values._version'))->assertOk();

        $this->assertEquals('Second draft', Entry::find($entry->id())->fromWorkingCopy()->value('title'));
    }

    #[Test]
    public function it_keeps_saving_after_publishing_from_the_same_form()
    {
        config(['statamic.revisions.enabled' => true]);
        $entry = $this->createEntry(revisions: true);

        $this->save($entry, 'Draft', $this->loadVersion($entry))->assertOk();

        $published = $this->actingAs($this->user)
            ->postJson($entry->publishUrl(), ['message' => 'Publish'])
            ->assertOk();

        $this->assertFalse(Entry::find($entry->id())->hasWorkingCopy());
        $this->save($entry, 'After publishing', $published->json('data.values._version'))->assertOk();
    }

    private function createEntry(bool $revisions = false)
    {
        $collection = tap(Collection::make('test')->revisionsEnabled($revisions))->save();

        return EntryFactory::collection($collection)
            ->slug('existing-entry')
            ->data(['title' => 'Existing Entry'])
            ->create();
    }

    private function createLocalization(array $data)
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US', 'name' => 'English'],
            'fr' => ['url' => '/fr/', 'locale' => 'fr_FR', 'name' => 'French'],
        ]);
        $this->setTestRoles(['test' => ['access cp', 'edit test entries', 'access en site', 'access fr site']]);
        $collection = tap(Collection::make('test')->sites(['en', 'fr'])->dated(true))->save();
        EntryFactory::collection($collection)->id('origin')->locale('en')->slug('a')->date('2024-01-01')->data(['title' => 'Same'])->create();

        return EntryFactory::collection($collection)->id('localization')->locale('fr')->origin('origin')->slug('a')->data($data)->create();
    }

    private function saveLocalization($localization, string $title, string $version)
    {
        return $this->actingAs($this->user)->patchJson($localization->updateUrl(), [
            'title' => $title,
            'slug' => 'a',
            'date' => '2024-01-01T00:00:00.000Z',
            '_localized' => ['title'],
            '_version' => $version,
        ]);
    }

    private function loadVersion($entry): string
    {
        $version = $this->actingAs($this->user)
            ->getJson($entry->editUrl())
            ->assertOk()
            ->json('values._version');

        $this->assertIsString($version);

        return $version;
    }

    private function save($entry, string $title, ?string $version)
    {
        return $this->actingAs($this->user)->patchJson($entry->updateUrl(), array_filter([
            'title' => $title,
            'slug' => 'existing-entry',
            '_version' => $version,
        ], fn ($value) => $value !== null));
    }
}

class StayOnFormAction extends Action
{
    public static function handle()
    {
        return 'stay_on_form';
    }

    public function run($items, $values)
    {
        //
    }
}
