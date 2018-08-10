<?php

namespace Tests\Data;

use Tests\TestCase;
use Illuminate\Support\Collection;
use Statamic\Data\Structures\Structure;
use Statamic\API\Structure as StructureAPI;

class StructureTest extends TestCase
{
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
    function it_gets_and_sets_the_data()
    {
        $structure = new Structure;
        $this->assertEquals([], $structure->data());

        $return = $structure->data(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $structure->data());
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
        $structure->data(['foo' => 'bar']);
        StructureAPI::shouldReceive('save')->with($structure)->once();

        $structure->save();
    }
}
