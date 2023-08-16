<?php

namespace Tests\Feature\Entries;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Facades\Event;
use Statamic\Events\EntrySaving;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateEntryTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_denies_access_if_you_dont_have_permission()
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

    /** @test */
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

    /** @test */
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

    /**
     * @test
     *
     * @dataProvider multipleSlugLangsProvider
     */
    public function slug_is_not_required_and_will_get_created_from_the_submitted_title_and_correct_language_if_slug_is_in_the_blueprint_and_the_submitted_slug_was_empty($lang, $expectedSlug)
    {
        Site::setConfig(['sites' => [
            'en' => array_merge(config('statamic.sites.sites.en'), ['lang' => $lang]),
        ]]);

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

    public function multipleSlugLangsProvider()
    {
        return [
            'English' => ['en', 'foo-bar-baz-aeoa'],
            'Danish' => ['da', 'foo-bar-baz-aeoeaa'], // danish replaces æøå with aeoeaa
        ];
    }

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
    public function auto_title_only_gets_saved_on_localization_when_different_from_origin()
    {
        Site::setConfig(['sites' => [
            'en' => ['locale' => 'en', 'url' => '/'],
            'fr' => ['locale' => 'fr', 'url' => '/fr/'],
        ]]);

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

    /** @test */
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

    /** @test */
    public function published_entry_gets_saved_to_working_copy()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function draft_entry_gets_saved_to_content()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function validation_error_returns_back()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function user_without_permission_to_manage_publish_state_cannot_change_publish_status()
    {
        $this->markTestIncomplete();
    }

    private function seedUserAndCollection()
    {
        $this->setTestRoles(['test' => ['access cp', 'edit test entries']]);
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

    private function update($entry, $attrs = [])
    {
        $payload = array_merge([
            'title' => 'Updated entry',
            'slug' => 'updated-entry',
        ], $attrs);

        return $this->patchJson($entry->updateUrl(), $payload);
    }
}
