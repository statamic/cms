<?php

namespace Tests\Feature\Entries;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\EntrySaving;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Folder;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Structures\CollectionStructure;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateEntryTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        $this->dir = __DIR__.'/tmp';

        config([
            'statamic.editions.pro' => true,
            'statamic.revisions.path' => $this->dir,
            'statamic.revisions.enabled' => true,
        ]);
    }

    public function tearDown(): void
    {
        Folder::delete($this->dir);
        parent::tearDown();
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_edit_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test'))->save();

        $entry = EntryFactory::collection($collection)
            ->slug('existing-entry')
            ->data(['title' => 'Existing Entry'])
            ->create();

        $this
            ->actingAs($user)
            ->update($entry)
            ->assertForbidden();

        $this->assertCount(1, Entry::all());
        $this->assertEquals('Existing Entry', $entry->fresh()->value('title'));
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_site_permission()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en_US', 'name' => 'English'],
            'fr' => ['url' => '/', 'locale' => 'fr_FR', 'name' => 'French'],
        ]);

        [$user, $collection] = $this->seedUserAndCollection();
        $collection->sites(['en', 'fr'])->save();
        Role::find('test')->removePermission('access en site');

        $entry = EntryFactory::collection($collection)
            ->slug('existing-entry')
            ->data(['title' => 'Existing Entry'])
            ->create();

        $this
            ->actingAs($user)
            ->update($entry)
            ->assertForbidden();

        $this->assertCount(1, Entry::all());
        $this->assertEquals('Existing Entry', $entry->fresh()->value('title'));
    }

    #[Test]
    public function entry_gets_updated()
    {
        [$user, $collection] = $this->seedUserAndCollection();

        $entry = EntryFactory::collection($collection)
            ->slug('existing-entry')
            ->data(['title' => 'Existing Entry'])
            ->create();

        $this->assertCount(1, Entry::all());

        $this
            ->actingAs($user)
            ->update($entry, ['title' => 'Updated Entry', 'slug' => 'updated-entry'])
            ->assertOk();

        // todo: assert about response content

        $this->assertCount(1, Entry::all());
        $entry = $entry->fresh();
        $this->assertEquals('Updated Entry', $entry->value('title'));
        $this->assertEquals('updated-entry', $entry->slug());
    }

    #[Test]
    public function date_gets_set_in_origin()
    {
        [$user, $collection] = $this->seedUserAndCollection();
        $collection->dated(true)->save();

        $entry = EntryFactory::collection($collection)
            ->slug('existing-entry')
            ->data(['title' => 'Existing Entry'])
            ->date('2021-01-01')
            ->create();

        $this
            ->actingAs($user)
            ->update($entry, [
                'title' => 'Updated Entry',
                'slug' => 'updated-entry',
                'date' => ['date' => '2021-02-02'],
                '_localized' => [], // empty to show that date doesn't need to be in here.
            ])
            ->assertOk();

        $entry = $entry->fresh();
        $this->assertEquals('2021-02-02', $entry->date()->format('Y-m-d'));
    }

    #[Test]
    #[DataProvider('savesDateProvider')]
    public function date_gets_set_in_localization_when_contained_in_localized_array($shouldBeInArray, $expectedDate)
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'fr' => ['url' => '/two/', 'locale' => 'fr'],
        ]);

        [$user, $collection] = $this->seedUserAndCollection();
        $collection->dated(true)->save();

        $entry = EntryFactory::collection($collection)
            ->slug('existing-entry')
            ->data(['title' => 'Existing Entry'])
            ->date('2021-01-01')
            ->create();

        $localized = EntryFactory::collection($collection)
            ->slug('existing-entry')
            ->data(['title' => 'Existing Entry'])
            ->origin($entry->id())
            ->locale('fr')
            ->create();

        $this
            ->actingAs($user)
            ->update($localized, [
                'title' => 'Updated Entry',
                'slug' => 'updated-entry',
                'date' => ['date' => '2021-02-02'],
                '_localized' => $shouldBeInArray ? ['date'] : [],
            ])
            ->assertOk();

        $localized = $localized->fresh();
        $this->assertEquals($expectedDate, $localized->date()->format('Y-m-d'));
    }

    public static function savesDateProvider()
    {
        return [
            'date is in localized array' => [true, '2021-02-02'],
            'date is not in localized array' => [false, '2021-01-01'],
        ];
    }

    #[Test]
    public function slug_is_not_required_and_will_get_created_from_the_submitted_title_if_slug_is_in_the_blueprint_and_the_submitted_slug_was_empty()
    {
        [$user, $collection] = $this->seedUserAndCollection();

        $entry = EntryFactory::collection($collection)
            ->slug('existing-entry')
            ->data(['title' => 'Existing Entry'])
            ->create();

        $this->assertTrue($entry->blueprint()->hasField('slug'));
        $this->assertCount(1, Entry::all());

        $this
            ->actingAs($user)
            ->update($entry, ['title' => 'Foo Bar Baz', 'slug' => ''])
            ->assertOk();

        $this->assertCount(1, Entry::all());
        $entry = $entry->fresh();
        $this->assertEquals('Foo Bar Baz', $entry->value('title'));
        $this->assertEquals('foo-bar-baz', $entry->slug());
        $this->assertEquals('foo-bar-baz.md', pathinfo($entry->path(), PATHINFO_BASENAME));
    }

    #[Test]
    #[DataProvider('multipleSlugLangsProvider')]
    public function slug_is_not_required_and_will_get_created_from_the_submitted_title_and_correct_language_if_slug_is_in_the_blueprint_and_the_submitted_slug_was_empty($lang, $expectedSlug)
    {
        $this->setSiteValue('en', 'lang', $lang);

        [$user, $collection] = $this->seedUserAndCollection();

        $entry = EntryFactory::collection($collection)
            ->slug('existing-entry')
            ->data(['title' => 'Existing Entry'])
            ->create();

        $this->assertTrue($entry->blueprint()->hasField('slug'));
        $this->assertCount(1, Entry::all());

        $this
            ->actingAs($user)
            ->update($entry, ['title' => 'Foo Bar Baz æøå', 'slug' => ''])
            ->assertOk();

        $this->assertCount(1, Entry::all());
        $entry = $entry->fresh();
        $this->assertEquals('Foo Bar Baz æøå', $entry->value('title'));
        $this->assertEquals($expectedSlug, $entry->slug());
        $this->assertEquals($expectedSlug.'.md', pathinfo($entry->path(), PATHINFO_BASENAME));
    }

    public static function multipleSlugLangsProvider()
    {
        return [
            'English' => ['en', 'foo-bar-baz-aeoa'],
            'Danish' => ['da', 'foo-bar-baz-aeoeaa'], // danish replaces æøå with aeoeaa
        ];
    }

    #[Test]
    public function slug_is_not_required_and_will_be_null_if_slug_is_not_in_the_blueprint()
    {
        [$user, $collection] = $this->seedUserAndCollection();
        $collection->requiresSlugs(false);

        $entry = EntryFactory::collection($collection)
            ->id('the-id')
            ->slug(null)
            ->data(['title' => 'Existing Entry'])
            ->create();

        $this->assertFalse($entry->blueprint()->hasField('slug'));
        $this->assertCount(1, Entry::all());

        $this
            ->actingAs($user)
            ->update($entry, ['title' => 'Foo Bar Baz', 'slug' => ''])
            ->assertOk();

        $this->assertCount(1, Entry::all());
        $entry = $entry->fresh();
        $this->assertEquals('Foo Bar Baz', $entry->value('title'));
        $this->assertNull($entry->slug());
        $this->assertEquals($entry->id().'.md', pathinfo($entry->path(), PATHINFO_BASENAME));
    }

    #[Test]
    public function slug_is_not_required_and_will_get_created_from_auto_generated_title_when_using_title_format()
    {
        [$user, $collection] = $this->seedUserAndCollection();
        $collection->titleFormats('Auto {foo}')->save();

        $entry = EntryFactory::collection($collection)
            ->slug('existing-entry')
            ->data(['title' => 'Existing Entry', 'foo' => 'bar'])
            ->create();

        $this->assertCount(1, Entry::all());

        $this
            ->actingAs($user)
            ->update($entry, ['title' => '', 'slug' => '', 'foo' => 'bar'])
            ->assertOk();

        $this->assertCount(1, Entry::all());
        $entry = $entry->fresh();
        $this->assertEquals('Auto bar', $entry->value('title'));
        $this->assertEquals('auto-bar', $entry->slug());
        $this->assertEquals('auto-bar.md', pathinfo($entry->path(), PATHINFO_BASENAME));
    }

    #[Test]
    public function submitted_slug_is_favored_over_auto_generated_title_when_using_title_format()
    {
        [$user, $collection] = $this->seedUserAndCollection();
        $collection->titleFormats('Auto {foo}')->save();

        $entry = EntryFactory::collection($collection)
            ->slug('existing-entry')
            ->data(['title' => 'Existing Entry', 'foo' => 'bar'])
            ->create();

        $this->assertCount(1, Entry::all());

        $this
            ->actingAs($user)
            ->update($entry, ['title' => '', 'slug' => 'manually-entered-slug', 'foo' => 'bar'])
            ->assertOk();

        $this->assertCount(1, Entry::all());
        $entry = $entry->fresh();
        $this->assertEquals('Auto bar', $entry->value('title'));
        $this->assertEquals('manually-entered-slug', $entry->slug());
        $this->assertEquals('manually-entered-slug.md', pathinfo($entry->path(), PATHINFO_BASENAME));
    }

    #[Test]
    public function slug_and_auto_title_get_generated_after_save()
    {
        // We want addons to be able to add/modify data that the auto title could rely on.
        // Since they only get the change after it's saved, we need to generate the slug and title after that.

        [$user, $collection] = $this->seedUserAndCollection();
        $collection->titleFormats('Auto {magic}')->save();

        Event::listen(EntrySaving::class, function (EntrySaving $event) {
            $event->entry->set('magic', 'Avada Kedavra');
        });

        $entry = EntryFactory::collection($collection)
            ->slug('existing-entry')
            ->data(['title' => 'Existing Entry', 'magic' => 'Alakazam'])
            ->create();

        $this->assertCount(1, Entry::all());

        $this
            ->actingAs($user)
            ->update($entry, ['title' => '', 'slug' => ''])
            ->assertOk();

        $this->assertCount(1, Entry::all());
        $entry = $entry->fresh();
        $this->assertEquals('Avada Kedavra', $entry->value('magic'));
        $this->assertEquals('Auto Avada Kedavra', $entry->value('title'));
        $this->assertEquals('auto-avada-kedavra', $entry->slug());
        $this->assertEquals('auto-avada-kedavra.md', pathinfo($entry->path(), PATHINFO_BASENAME));
    }

    #[Test]
    public function auto_title_only_gets_saved_on_localization_when_different_from_origin()
    {
        $this->setSites([
            'en' => ['locale' => 'en', 'url' => '/'],
            'fr' => ['locale' => 'fr', 'url' => '/fr/'],
        ]);

        [$user, $collection] = $this->seedUserAndCollection();
        $collection->sites(['en', 'fr']);
        $collection->titleFormats('Auto {foo}')->save();

        $this->seedBlueprintFields($collection, [
            'foo' => ['type' => 'text'],
        ]);

        $origin = EntryFactory::collection($collection)
            ->locale('en')
            ->slug('origin')
            ->data(['foo' => 'bar'])
            ->create();

        $localization = EntryFactory::collection($collection)
            ->locale('fr')
            ->origin($origin)
            ->slug('localization')
            ->create();

        $this
            ->actingAs($user)
            ->update($localization, [
                'foo' => 'le bar',
                '_localized' => ['foo'],
            ])
            ->assertOk();

        $localization = $localization->fresh();
        $this->assertEquals('le bar', $localization->foo);
        $this->assertEquals('Auto le bar', $localization->title);
        $this->assertEquals('Auto le bar', $localization->get('title'));

        $this
            ->actingAs($user)
            ->update($localization, [
                '_localized' => [], // foo is intentionally missing
            ])
            ->assertOk();

        $localization = $localization->fresh();
        $this->assertEquals('bar', $localization->foo);
        $this->assertEquals('Auto bar', $localization->title);
        $this->assertNull($localization->get('title'));
    }

    #[Test]
    public function it_can_validate_against_published_value()
    {
        [$user, $collection] = $this->seedUserAndCollection();

        $this->seedBlueprintFields($collection, [
            'test_field' => ['validate' => 'required_if:published,true'],
        ]);

        $entry = EntryFactory::collection($collection)
            ->slug('existing-entry')
            ->data(['title' => 'Existing Entry', 'foo' => 'bar'])
            ->create();

        $this
            ->actingAs($user)
            ->update($entry, ['title' => 'Test', 'slug' => 'manually-entered-slug', 'published' => true])
            ->assertStatus(422);
    }

    #[Test]
    public function published_entry_gets_saved_to_working_copy()
    {
        [$user, $collection] = $this->seedUserAndCollection(true);

        $this->seedBlueprintFields($collection, [
            'revisable' => ['type' => 'text'],
            'non_revisable' => ['type' => 'text', 'revisable' => false],
        ]);

        $entry = EntryFactory::id('1')
            ->slug('test')
            ->collection('test')
            ->data(['title' => 'Revisable Test', 'published' => true])
            ->create();

        $this
            ->actingAs($user)
            ->update($entry, [
                'revisable' => 'revise me',
                'non_revisable' => 'no revisions for you',
            ])
            ->assertOk();

        $entry = Entry::find($entry->id());
        $this->assertEquals('no revisions for you', $entry->non_revisable);
        $this->assertEquals('Revisable Test', $entry->title);
        $this->assertEquals('test', $entry->slug());
        $this->assertNull($entry->revisable);

        $workingCopy = $entry->fromWorkingCopy();
        $this->assertEquals('updated-entry', $workingCopy->slug());
        $this->assertEquals([
            'title' => 'Updated entry',
            'revisable' => 'revise me',
            'non_revisable' => 'no revisions for you',
            'published' => true,
        ], $workingCopy->data()->all());
    }

    #[Test]
    public function draft_entry_gets_saved_to_content()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function validation_error_returns_back()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function user_without_permission_to_manage_publish_state_cannot_change_publish_status()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function validates_max_depth()
    {
        [$user, $collection] = $this->seedUserAndCollection();

        $structure = (new CollectionStructure)->maxDepth(2)->expectsRoot(true);
        $collection->structure($structure)->save();

        EntryFactory::collection('test')->id('home')->slug('home')->data(['title' => 'Home', 'foo' => 'bar'])->create();
        EntryFactory::collection('test')->id('about')->slug('about')->data(['title' => 'About', 'foo' => 'baz'])->create();
        EntryFactory::collection('test')->id('team')->slug('team')->data(['title' => 'Team'])->create();

        $entry = EntryFactory::collection($collection)
            ->id('existing-entry')
            ->slug('existing-entry')
            ->data(['title' => 'Existing Entry', 'foo' => 'bar'])
            ->create();

        $collection->structure()->in('en')->tree([
            ['entry' => 'home'],
            ['entry' => 'about', 'children' => [
                ['entry' => 'team'],
            ]],
            ['entry' => 'existing-entry'],
        ])->save();

        $this
            ->actingAs($user)
            ->update($entry, ['title' => 'Existing Entry', 'slug' => 'existing-entry', 'parent' => ['team']]) // This would make it 3 levels deep, so it should fail.
            ->assertUnprocessable();
    }

    #[Test]
    public function does_not_validate_max_depth_when_collection_max_depth_is_null()
    {
        [$user, $collection] = $this->seedUserAndCollection();

        $structure = (new CollectionStructure)->expectsRoot(true);
        $collection->structure($structure)->save();

        EntryFactory::collection('test')->id('home')->slug('home')->data(['title' => 'Home', 'foo' => 'bar'])->create();
        EntryFactory::collection('test')->id('about')->slug('about')->data(['title' => 'About', 'foo' => 'baz'])->create();
        EntryFactory::collection('test')->id('team')->slug('team')->data(['title' => 'Team'])->create();

        $entry = EntryFactory::collection($collection)
            ->id('existing-entry')
            ->slug('existing-entry')
            ->data(['title' => 'Existing Entry', 'foo' => 'bar'])
            ->create();

        $collection->structure()->in('en')->tree([
            ['entry' => 'home'],
            ['entry' => 'about', 'children' => [
                ['entry' => 'team'],
            ]],
            ['entry' => 'existing-entry'],
        ])->save();

        $this
            ->actingAs($user)
            ->update($entry, ['title' => 'Existing Entry', 'slug' => 'existing-entry', 'parent' => ['team']]) // Since we have no max depth set, this should be fine.
            ->assertOk();
    }

    private function seedUserAndCollection(bool $enableRevisions = false)
    {
        $this->setTestRoles(['test' => [
            'access cp',
            'edit test entries',
            'access en site',
            'access fr site',
        ]]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test')->revisionsEnabled($enableRevisions))->save();

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

    private function update($entry, $attrs = [])
    {
        $payload = array_merge([
            'title' => 'Updated entry',
            'slug' => 'updated-entry',
        ], $attrs);

        return $this->patchJson($entry->updateUrl(), $payload);
    }
}
