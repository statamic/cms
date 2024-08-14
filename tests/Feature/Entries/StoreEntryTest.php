<?php

namespace Tests\Feature\Entries;

use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\EntrySaving;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StoreEntryTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test'))->save();

        $this
            ->actingAs($user)
            ->submit($collection, [])
            ->assertForbidden();
    }

    #[Test]
    public function entry_gets_created()
    {
        [$user, $collection] = $this->seedUserAndCollection();

        $this->assertCount(0, Entry::all());

        $this
            ->actingAs($user)
            ->submit($collection, ['title' => 'My Entry', 'slug' => 'my-entry'])
            ->assertOk();

        // todo: assert response contents

        $this->assertCount(1, Entry::all());
        $entry = Entry::all()->first();
        $this->assertEquals('My Entry', $entry->value('title'));
        $this->assertEquals('my-entry', $entry->slug());
    }

    #[Test]
    public function slug_is_not_required_and_will_get_created_from_the_submitted_title_if_slug_is_in_blueprint()
    {
        [$user, $collection] = $this->seedUserAndCollection();

        $this->assertTrue($collection->entryBlueprint()->hasField('slug'));
        $this->assertCount(0, Entry::all());

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit($collection, ['title' => 'Test Entry', 'slug' => ''])
            ->assertOk();

        $this->assertCount(1, Entry::all());
        $entry = Entry::all()->first();
        $this->assertEquals('Test Entry', $entry->value('title'));
        $this->assertEquals('test-entry', $entry->slug());
        $this->assertEquals('test-entry.md', pathinfo($entry->path(), PATHINFO_BASENAME));
    }

    #[Test]
    #[DataProvider('multipleSlugLangsProvider')]
    public function slug_is_not_required_and_will_get_created_from_the_submitted_title_if_slug_is_in_blueprint_and_use_entry_language($lang, $expectedSlug)
    {
        $this->setSiteValue('en', 'lang', $lang);

        [$user, $collection] = $this->seedUserAndCollection();

        $this->assertTrue($collection->entryBlueprint()->hasField('slug'));
        $this->assertCount(0, Entry::all());

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit($collection, ['title' => 'Test Entry æøå', 'slug' => ''])
            ->assertOk();

        $this->assertCount(1, Entry::all());
        $entry = Entry::all()->first();
        $this->assertEquals('Test Entry æøå', $entry->value('title'));
        $this->assertEquals($expectedSlug, $entry->slug());
        $this->assertEquals($expectedSlug.'.md', pathinfo($entry->path(), PATHINFO_BASENAME));
    }

    public static function multipleSlugLangsProvider()
    {
        return [
            'English' => ['en', 'test-entry-aeoa'],
            'Danish' => ['da', 'test-entry-aeoeaa'], // danish replaces æøå with aeoeaa
        ];
    }

    #[Test]
    public function slug_is_not_required_and_will_be_null_if_slug_is_not_in_the_blueprint()
    {
        [$user, $collection] = $this->seedUserAndCollection();
        $collection->requiresSlugs(false);

        $this->assertFalse($collection->entryBlueprint()->hasField('slug'));
        $this->assertCount(0, Entry::all());

        $this
            ->from('/original')
            ->actingAs($user)
            ->submit($collection, ['title' => 'Test Entry', 'slug' => ''])
            ->assertOk();

        $this->assertCount(1, Entry::all());
        $entry = Entry::all()->first();
        $this->assertEquals('Test Entry', $entry->value('title'));
        $this->assertNull($entry->slug());
        $this->assertEquals($entry->id().'.md', pathinfo($entry->path(), PATHINFO_BASENAME));
    }

    #[Test]
    public function slug_is_not_required_and_will_get_created_from_auto_generated_title_when_using_title_format()
    {
        [$user, $collection] = $this->seedUserAndCollection();
        $collection->titleFormats('Auto {foo}')->save();
        $this->seedBlueprintFields($collection, ['foo' => ['type' => 'text']]);

        $this->assertCount(0, Entry::all());

        $this
            ->actingAs($user)
            ->submit($collection, [
                'title' => '',
                'slug' => '',
                'foo' => 'bar',
            ])->assertOk();

        $this->assertCount(1, Entry::all());
        $entry = Entry::all()->first();
        $this->assertEquals('Auto bar', $entry->value('title'));
        $this->assertEquals('auto-bar', $entry->slug());
        $this->assertEquals('auto-bar.md', pathinfo($entry->path(), PATHINFO_BASENAME));
    }

    #[Test]
    public function submitted_slug_is_favored_over_auto_generated_title_when_using_title_format()
    {
        [$user, $collection] = $this->seedUserAndCollection();
        $collection->titleFormats('Auto {foo}')->save();
        $this->seedBlueprintFields($collection, ['foo' => ['type' => 'text']]);

        $this->assertCount(0, Entry::all());

        $this
            ->actingAs($user)
            ->submit($collection, [
                'title' => '',
                'slug' => 'manually-entered-slug',
                'foo' => 'bar',
            ])->assertOk();

        $this->assertCount(1, Entry::all());
        $entry = Entry::all()->first();
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

        $this->assertCount(0, Entry::all());

        $this
            ->actingAs($user)
            ->submit($collection, ['title' => '', 'slug' => ''])->assertOk();

        $this->assertCount(1, Entry::all());
        $entry = Entry::all()->first();
        $this->assertEquals('Avada Kedavra', $entry->value('magic'));
        $this->assertEquals('Auto Avada Kedavra', $entry->value('title'));
        $this->assertEquals('auto-avada-kedavra', $entry->slug());
        $this->assertEquals('auto-avada-kedavra.md', pathinfo($entry->path(), PATHINFO_BASENAME));
    }

    #[Test]
    public function it_can_validate_against_published_value()
    {
        [$user, $collection] = $this->seedUserAndCollection();

        $this->seedBlueprintFields($collection, [
            'test_field' => ['validate' => 'required_if:published,true'],
        ]);

        $this
            ->actingAs($user)
            ->submit($collection, ['title' => 'Test', 'slug' => 'manually-entered-slug', 'published' => true])
            ->assertStatus(422);
    }

    private function seedUserAndCollection()
    {
        $this->setTestRoles(['test' => ['access cp', 'create test entries']]);
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

    private function submit($collection, $attrs = [])
    {
        $url = cp_route('collections.entries.store', [$collection->handle(), 'en']);

        $payload = array_merge([
            'title' => 'Test entry',
            'slug' => 'test-entry',
        ], $attrs);

        return $this->postJson($url, $payload);
    }
}
