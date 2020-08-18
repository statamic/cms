<?php

namespace Tests\Data\Structures;

use Illuminate\Support\Collection as LaravelCollection;
use Statamic\Contracts\Entries\Collection as StatamicCollection;
use Statamic\Facades;
use Statamic\Structures\Nav;
use Tests\PreventSavingStacheItemsToDisk;

class NavTest extends StructureTestCase
{
    use PreventSavingStacheItemsToDisk;

    public function structure($handle = null)
    {
        return (new Nav)->handle($handle);
    }

    /** @test */
    public function it_gets_and_sets_the_handle()
    {
        $structure = $this->structure();
        $this->assertNull($structure->handle());

        $return = $structure->handle('test');

        $this->assertEquals('test', $structure->handle());
        $this->assertEquals($structure, $return);
    }

    /** @test */
    public function it_gets_and_sets_the_title()
    {
        $structure = $this->structure('test');

        // No title set falls back to uppercased version of the handle
        $this->assertEquals('Test', $structure->title());

        $return = $structure->title('Explicitly set title');

        $this->assertEquals('Explicitly set title', $structure->title());
        $this->assertEquals($structure, $return);
    }

    /** @test */
    public function it_saves_the_nav_through_the_api()
    {
        $nav = $this->structure();

        Facades\Nav::shouldReceive('save')->with($nav)->once();

        $this->assertTrue($nav->save());
    }

    /** @test */
    public function it_deletes_through_the_api()
    {
        $nav = $this->structure();

        Facades\Nav::shouldReceive('delete')->with($nav)->once();

        $this->assertTrue($nav->delete());
    }

    /** @test */
    public function collections_can_be_get_and_set()
    {
        $nav = $this->structure();
        $collectionOne = tap(Facades\Collection::make('one'))->save();
        $collectionTwo = tap(Facades\Collection::make('two'))->save();

        $collections = $nav->collections();
        $this->assertInstanceOf(LaravelCollection::class, $collections);
        $this->assertCount(0, $collections);

        $return = $nav->collections(['one', 'two']);

        $this->assertSame($nav, $return);
        $collections = $nav->collections();
        $this->assertInstanceOf(LaravelCollection::class, $collections);
        $this->assertEveryItemIsInstanceOf(StatamicCollection::class, $collections);
        $this->assertCount(2, $collections);
        $this->assertEquals([$collectionOne, $collectionTwo], $collections->all());
    }

    /** @test */
    public function it_has_cp_urls()
    {
        $nav = $this->structure('test');

        $this->assertEquals('http://localhost/cp/navigation/test', $nav->showUrl());
        $this->assertEquals('http://localhost/cp/navigation/test?foo=bar', $nav->showUrl(['foo' => 'bar']));
        $this->assertEquals('http://localhost/cp/navigation/test/edit', $nav->editUrl());
        $this->assertEquals('http://localhost/cp/navigation/test', $nav->deleteUrl());
    }

    /** @test */
    public function it_has_no_route()
    {
        $this->assertNull($this->structure('test')->route('en'));
    }
}
