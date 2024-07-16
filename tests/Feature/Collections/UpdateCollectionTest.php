<?php

namespace Tests\Feature\Collections;

use Facades\Statamic\Fields\BlueprintRepository;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Tests\Fakes\FakeBlueprintRepository;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateCollectionTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $collection = Collection::make('test')->save();

        $this
            ->from('/original')
            ->actingAs($this->userWithoutPermission())
            ->update($collection)
            ->assertRedirect('/original')
            ->assertSessionHas('error');
    }

    #[Test]
    public function it_updates_a_collection()
    {
        $this->withoutExceptionHandling();
        config(['statamic.amp.enabled' => true]);

        $collection = tap(
            Collection::make('test')
                ->title('Original title')
                ->dated(false)
                ->template('original-template')
                ->layout('original-layout')
                ->defaultPublishState(true)
                ->sortDirection('asc')
        )->save();
        $this->assertCount(1, Collection::all());
        $this->assertEquals('Original title', $collection->title());
        $this->assertFalse($collection->dated());
        $this->assertEquals('public', $collection->pastDateBehavior());
        $this->assertEquals('public', $collection->futureDateBehavior());
        $this->assertEquals('original-template', $collection->template());
        $this->assertEquals('original-layout', $collection->layout());
        $this->assertTrue($collection->defaultPublishState());
        $this->assertEquals('asc', $collection->sortDirection());

        $this
            ->actingAs($this->userWithPermission())
            ->update($collection, [
                'title' => 'Updated title',
                'dated' => true,
                'past_date_behavior' => 'private',
                'future_date_behavior' => 'hidden',
                'template' => 'updated-template',
                'layout' => 'updated-layout',
                'default_publish_state' => false,
                'sort_direction' => 'desc',
            ])
            ->assertOk();

        $this->assertCount(1, Collection::all());
        $updated = Collection::all()->first();
        $this->assertEquals('Updated title', $updated->title());
        $this->assertTrue($updated->dated());
        $this->assertEquals('private', $collection->pastDateBehavior());
        $this->assertEquals('hidden', $collection->futureDateBehavior());
        $this->assertEquals('updated-template', $updated->template());
        $this->assertEquals('updated-layout', $updated->layout());
        $this->assertFalse($updated->defaultPublishState());
        $this->assertEquals('desc', $updated->sortDirection());
        // structure
    }

    #[Test]
    public function setting_links_to_true_will_create_a_blueprint_if_it_doesnt_already_exist()
    {
        BlueprintRepository::swap(new FakeBlueprintRepository(BlueprintRepository::getFacadeRoot()));

        $collection = tap(Collection::make('test'))->save();
        Blueprint::make('not_link')->setNamespace('collections.test')->save();
        $this->assertCount(1, Blueprint::in('collections.test'));

        $this
            ->actingAs($this->userWithPermission())
            ->update($collection, ['links' => true])
            ->assertOk();

        $this->assertCount(2, Blueprint::in('collections.test'));
    }

    #[Test]
    public function setting_links_to_true_will_do_nothing_if_an_existing_link_blueprint_already_exists()
    {
        BlueprintRepository::swap(new FakeBlueprintRepository(BlueprintRepository::getFacadeRoot()));

        $collection = tap(Collection::make('test'))->save();
        Blueprint::make('link')->setNamespace('collections.test')->setContents(['title' => 'Existing'])->save();
        $this->assertCount(1, Blueprint::in('collections.test'));

        $this
            ->actingAs($this->userWithPermission())
            ->update($collection, ['links' => true])
            ->assertOk();

        $this->assertCount(1, Blueprint::in('collections.test'));
        $this->assertEquals('Existing', Blueprint::find('collections.test.link')->title());
    }

    #[Test]
    public function setting_links_to_false_will_delete_the_blueprint_if_exists()
    {
        BlueprintRepository::swap(new FakeBlueprintRepository(BlueprintRepository::getFacadeRoot()));

        $collection = tap(Collection::make('test'))->save();
        Blueprint::make('link')->setNamespace('collections.test')->save();
        $this->assertCount(1, Blueprint::in('collections.test'));

        $this
            ->actingAs($this->userWithPermission())
            ->update($collection, ['links' => false])
            ->assertOk();

        $this->assertCount(0, Blueprint::in('collections.test'));
    }

    #[Test]
    public function settings_links_to_true_will_also_create_the_default_blueprint_if_none_exist()
    {
        // this is so that you aren't left in awkward situation where there's only a links blueprint.

        BlueprintRepository::swap(new FakeBlueprintRepository(BlueprintRepository::getFacadeRoot()));

        $collection = tap(Collection::make('test'))->save();
        $this->assertCount(0, Blueprint::in('collections.test'));

        $this
            ->actingAs($this->userWithPermission())
            ->update($collection, ['links' => true])
            ->assertOk();

        $this->assertCount(2, $blueprints = Blueprint::in('collections.test'));
        $this->assertEquals(['test', 'link'], $blueprints->map->handle()->values()->all());
    }

    private function userWithoutPermission()
    {
        $this->setTestRoles(['test' => ['access cp']]);

        return tap(User::make()->assignRole('test'))->save();
    }

    private function userWithPermission()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure collections']]);

        return tap(User::make()->assignRole('test'))->save();
    }

    private function update($collection, $params = [])
    {
        $params = array_merge([
            'title' => 'Updated title',
            'dated' => false,
            'past_date_behavior' => 'public',
            'future_date_behavior' => 'public',
            'template' => 'updated-template',
            'layout' => 'updated-layout',
            'default_publish_state' => true,
            'ampable' => false,
        ], $params);

        return $this->patch(cp_route('collections.update', $collection->handle()), $params);
    }
}
