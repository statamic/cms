<?php

namespace Tests\Feature\Entries;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Folder;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EditEntryTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        $this->markTestIncomplete('Needs to be updated for localization and when revisions are enabled/disabled.');
        parent::setUp();
        $this->dir = __DIR__.'/tmp';
        config(['statamic.revisions.path' => $this->dir]);
    }

    public function tearDown(): void
    {
        Folder::delete($this->dir);
        parent::tearDown();
    }

    /** @test */
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test');
        $entry = EntryFactory::slug('test')->collection('blog')->create();

        $this
            ->from('/original')
            ->actingAs($user)
            ->get($entry->editUrl())
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    /** @test */
    public function it_shows_the_entry_form()
    {
        BlueprintRepository::shouldReceive('find')->with('test')->andReturn((new Blueprint)->setContents(['fields' => [
            ['handle' => 'foo', 'field' => ['type' => 'text']],
            ['handle' => 'unused', 'field' => ['type' => 'text']],
        ]]));
        $this->setTestRoles(['test' => ['access cp', 'edit blog entries']]);
        $user = User::make()->assignRole('test')->save();

        $entry = EntryFactory::slug('test')
            ->collection('blog')
            ->data([
                'blueprint' => 'test',
                'title' => 'Test',
                'foo' => 'bar',
            ])
            ->create();

        $this
            ->actingAs($user)
            ->get($entry->editUrl())
            ->assertSuccessful()
            ->assertViewHas('values', [
                'foo' => 'bar',
                'unused' => null,
                'title' => 'Test',
                'slug' => 'test',
            ])
            ->assertViewHas('readOnly', false);
    }

    /** @test */
    public function it_overrides_values_from_the_working_copy()
    {
        BlueprintRepository::shouldReceive('find')->with('test')->andReturn((new Blueprint)->setContents(['fields' => [
            ['handle' => 'foo', 'field' => ['type' => 'text']],
            ['handle' => 'unused', 'field' => ['type' => 'text']],
        ]]));
        $this->setTestRoles(['test' => ['access cp', 'edit blog entries']]);
        $user = User::make()->assignRole('test')->save();

        $entry = EntryFactory::slug('test')
            ->collection('blog')
            ->data([
                'blueprint' => 'test',
                'title' => 'Test',
                'foo' => 'bar',
            ])
            ->create();

        $workingCopy = $entry->makeWorkingCopy();
        $attributes = $workingCopy->attributes();
        $attributes['data']['foo'] = 'Edited in working copy';
        $workingCopy->attributes($attributes)->save();

        $this
            ->actingAs($user)
            ->get($entry->editUrl())
            ->assertSuccessful()
            ->assertViewHas('values', [
                'foo' => 'Edited in working copy',
                'unused' => null,
                'title' => 'Test',
                'slug' => 'test',
            ])
            ->assertViewHas('readOnly', false);
    }

    /** @test */
    public function it_marks_as_read_only_if_you_only_have_view_permission()
    {
        BlueprintRepository::shouldReceive('find')->with('test')->andReturn(new Blueprint);
        $this->setTestRoles(['test' => ['access cp', 'view blog entries']]);
        $user = User::make()->assignRole('test')->save();

        $entry = EntryFactory::slug('test')
            ->collection('blog')
            ->data(['blueprint' => 'test'])
            ->create();

        $this
            ->actingAs($user)
            ->get($entry->editUrl())
            ->assertSuccessful()
            ->assertViewHas('readOnly', true);
    }
}
