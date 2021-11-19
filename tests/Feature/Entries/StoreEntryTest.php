<?php

namespace Tests\Feature\Entries;

use Facades\Statamic\Fields\BlueprintRepository;
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

    /** @test */
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();
        $collection = tap(Collection::make('test'))->save();

        $this
            ->actingAs($user)
            ->from('/original')
            ->submit($collection, [])
            ->assertRedirect('/original');
    }

    /** @test */
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

    /** @test */
    public function slug_is_not_required_and_will_get_created_from_the_submitted_title()
    {
        [$user, $collection] = $this->seedUserAndCollection();

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
    }

    /** @test */
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

        return $this->post($url, $payload);
    }
}
