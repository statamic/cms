<?php

namespace Tests\Data\Assets;

use Illuminate\Support\Facades\Storage;
use Statamic\Facades\AssetContainer;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AssetQueryBuilderTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        tap(Storage::fake('test'))->getDriver()->getConfig()->set('url', '/assets');

        Storage::disk('test')->put('a.jpg', '');
        Storage::disk('test')->put('b.txt', '');
        Storage::disk('test')->put('c.txt', '');
        Storage::disk('test')->put('d.jpg', '');
        Storage::disk('test')->put('e.jpg', '');
        Storage::disk('test')->put('f.jpg', '');

        $this->container = tap(AssetContainer::make('test')->disk('test'))->save();
    }

    /** @test */
    public function it_can_get_assets()
    {
        $assets = $this->container->queryAssets()->get();

        $this->assertCount(6, $assets);
        $this->assertEquals(['a', 'b', 'c', 'd', 'e', 'f'], $assets->map->filename()->all());
    }

    /** @test **/
    public function assets_are_found_using_or_where()
    {
        $assets = $this->container->queryAssets()->where('filename', 'a')->orWhere('filename', 'c')->get();

        $this->assertCount(2, $assets);
        $this->assertEquals(['a', 'c'], $assets->map->filename()->all());
    }

    /** @test **/
    public function assets_are_found_using_or_where_in()
    {
        $assets = $this->container->queryAssets()
            ->whereIn('filename', ['a', 'b'])
            ->orWhereIn('filename', ['a', 'd'])
            ->orWhereIn('extension', ['jpg'])
            ->get();

        $this->assertCount(5, $assets);
        $this->assertEquals(['a', 'b', 'd', 'e', 'f'], $assets->map->filename()->all());
    }

    /** @test **/
    public function assets_are_found_using_or_where_not_in()
    {
        $assets = $this->container->queryAssets()
            ->whereNotIn('filename', ['a', 'b'])
            ->orWhereNotIn('filename', ['a', 'f'])
            ->orWhereNotIn('extension', ['txt'])
            ->get();

        $this->assertCount(2, $assets);
        $this->assertEquals(['d', 'e'], $assets->map->filename()->all());
    }
}
