<?php

namespace Tests\Data\Structures;

use Tests\TestCase;
use Statamic\Facades\Entry;
use Illuminate\Support\Collection;
use Statamic\Structures\Page;
use Statamic\Structures\Pages;
use Statamic\Structures\Structure;
use Statamic\Facades\Structure as StructureAPI;

class StructureTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $stache = $this->app->make('stache');
        $dir = __DIR__.'/../../Stache/__fixtures__';
        $stache->store('collections')->directory($dir . '/content/collections');
        $stache->store('entries')->directory($dir . '/content/collections');
    }

    /** @test */
    function it_gets_and_sets_the_handle()
    {
        $structure = new Structure;
        $this->assertNull($structure->handle());

        $return = $structure->handle('test');

        $this->assertEquals('test', $structure->handle());
        $this->assertEquals($structure, $return);
    }

    /** @test */
    function it_gets_and_sets_the_title()
    {
        $structure = (new Structure)->handle('test');

        // No title set falls back to uppercased version of the handle
        $this->assertEquals('Test', $structure->title());

        $return = $structure->title('Explicitly set title');

        $this->assertEquals('Explicitly set title', $structure->title());
        $this->assertEquals($structure, $return);
    }

    /** @test */
    function it_saves_the_structure_through_the_api()
    {
        $structure = new Structure;
        StructureAPI::shouldReceive('save')->with($structure)->once();

        $structure->save();
    }
}
