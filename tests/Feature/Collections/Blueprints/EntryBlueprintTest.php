<?php

namespace Tests\Feature\Collections\Blueprints;

use Statamic\Facades;
use Statamic\Facades\Collection;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EntryBlueprintTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_gets_the_fallback_collection_entry_blueprint()
    {
        $collection = tap(Collection::make('test'))->save();
        $blueprint = $collection->entryBlueprint();

        $fallbackBlueprint = $collection
            ->ensureEntryBlueprintFields($collection->fallbackEntryBlueprint())
            ->setParent($collection);

        $this->assertEquals($blueprint, $fallbackBlueprint);
    }

    /** @test */
    public function it_gets_first_collection_entry_blueprint_when_has_multiple_blueprints()
    {
        $collection = Collection::make('entry-blueprints')->save();
        Facades\Blueprint::make('entry-one')->setNamespace('collections.entry-blueprints')->save();
        Facades\Blueprint::make('entry-two')->setNamespace('collections.entry-blueprints')->save();

        $blueprints = $collection->entryBlueprints();

        /** @var \Statamic\Fields\Blueprint $first */
        $first = $blueprints->first();

        $this->assertEquals('entry-one', $first->handle());
        $this->assertEquals($collection->entryBlueprint(), $first);
    }

    /** @test */
    public function it_fails_to_get_non_existing_custom_collection_entry_blueprint()
    {
        $collection = Collection::make('entry-blueprints')->save();
        Facades\Blueprint::make('entry-one')->setNamespace('collections.entry-blueprints')->save();
        Facades\Blueprint::make('entry-two')->setNamespace('collections.entry-blueprints')->save();

        $blueprint = $collection->entryBlueprint('foo');

        $this->assertNull($blueprint);
    }


    /** @test */
    public function it_gets_custom_collection_entry_blueprint()
    {
        $collection = Collection::make('entry-blueprints')->save();
        Facades\Blueprint::make('entry-one')->setNamespace('collections.entry-blueprints')->save();
        Facades\Blueprint::make('entry-two')->setNamespace('collections.entry-blueprints')->save();

        $blueprintTwo = $collection->entryBlueprints()->keyBy->handle()->get('entry-two');
        $blueprint = $collection->entryBlueprint('entry-two');

        $this->assertEquals('entry-two', $blueprint->handle());
        $this->assertEquals($blueprintTwo, $blueprint);
    }
}
