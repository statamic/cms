<?php

namespace Tests\Data\Entries;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Fields\BlueprintRepository;
use Tests\TestCase;

class CollectionAddonBlueprintTest extends TestCase
{
    private string $directory;

    public function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir().'/statamic-addon-blueprints-'.uniqid();
        mkdir($this->directory, 0777, true);
        file_put_contents($this->directory.'/people.yaml', "title: People\n");

        Blueprint::swap(app(BlueprintRepository::class)->setDirectories($this->directory.'/site'));

        // The way AddonServiceProvider registers an addon's resources/blueprints directory.
        Blueprint::addNamespace('collections.people', $this->directory);
    }

    public function tearDown(): void
    {
        @unlink($this->directory.'/people.yaml');
        @rmdir($this->directory);

        parent::tearDown();
    }

    #[Test]
    public function it_gets_entry_blueprints_from_an_addon_directory()
    {
        $blueprints = Collection::make('people')->entryBlueprints();

        $this->assertCount(1, $blueprints);
        $this->assertSame('people', $blueprints->first()->handle());
        $this->assertSame('collections.people', $blueprints->first()->namespace());
        $this->assertSame('People', $blueprints->first()->title());
    }
}
