<?php

namespace Tests\Feature\Entries;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
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
            ->from('/original')
            ->update($entry)
            ->assertRedirect('/original');

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
    public function slug_is_not_required_and_will_get_created_from_the_submitted_title()
    {
        [$user, $collection] = $this->seedUserAndCollection();

        $entry = EntryFactory::collection($collection)
            ->slug('existing-entry')
            ->data(['title' => 'Existing Entry'])
            ->create();

        $this->assertCount(1, Entry::all());

        $this
            ->actingAs($user)
            ->update($entry, ['title' => 'Foo Bar Baz', 'slug' => ''])
            ->assertOk();

        $this->assertCount(1, Entry::all());
        $entry = $entry->fresh();
        $this->assertEquals('Foo Bar Baz', $entry->value('title'));
        $this->assertEquals('foo-bar-baz', $entry->slug());
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

    private function update($entry, $attrs = [])
    {
        $payload = array_merge([
            'title' => 'Updated entry',
            'slug' => 'updated-entry',
        ], $attrs);

        return $this->patch($entry->updateUrl(), $payload);
    }
}
