<?php

namespace Tests\Data\Assets;

use Illuminate\Support\Facades\Storage;
use Statamic\Facades\Asset;
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

    /** @test **/
    public function assets_are_found_using_where_date()
    {
        Asset::find('test::a.jpg')->data(['test_date' => 1637008264])->save();
        Asset::find('test::b.txt')->data(['test_date' => '2021-11-14 09:00:00'])->save();
        Asset::find('test::c.txt')->data(['test_date' => '2021-11-15'])->save();
        Asset::find('test::d.jpg')->data(['test_date' => 1627008264])->save();
        Asset::find('test::e.jpg')->data(['test_date' => null])->save();

        $assets = $this->container->queryAssets()->whereDate('test_date', '2021-11-15')->get();

        $this->assertCount(2, $assets);
        $this->assertEquals(['a', 'c'], $assets->map->filename()->all());

        $assets = $this->container->queryAssets()->whereDate('test_date', 1637000264)->get();

        $this->assertCount(2, $assets);
        $this->assertEquals(['a', 'c'], $assets->map->filename()->all());

        $assets = $this->container->queryAssets()->whereDate('test_date', '>=', '2021-11-15')->get();

        $this->assertCount(2, $assets);
        $this->assertEquals(['a', 'c'], $assets->map->filename()->all());
    }

    /** @test **/
    public function assets_are_found_using_where_month()
    {
        Asset::find('test::a.jpg')->data(['test_date' => 1637008264])->save();
        Asset::find('test::b.txt')->data(['test_date' => '2021-11-14 09:00:00'])->save();
        Asset::find('test::c.txt')->data(['test_date' => '2021-11-15'])->save();
        Asset::find('test::d.jpg')->data(['test_date' => 1627008264])->save();
        Asset::find('test::e.jpg')->data(['test_date' => null])->save();

        $assets = $this->container->queryAssets()->whereMonth('test_date', 11)->get();

        $this->assertCount(3, $assets);
        $this->assertEquals(['a', 'b', 'c'], $assets->map->filename()->all());

        $assets = $this->container->queryAssets()->whereMonth('test_date', '<', 11)->get();

        $this->assertCount(1, $assets);
        $this->assertEquals(['d'], $assets->map->filename()->all());
    }

    /** @test **/
    public function assets_are_found_using_where_day()
    {
        Asset::find('test::a.jpg')->data(['test_date' => 1637008264])->save();
        Asset::find('test::b.txt')->data(['test_date' => '2021-11-14 09:00:00'])->save();
        Asset::find('test::c.txt')->data(['test_date' => '2021-11-15'])->save();
        Asset::find('test::d.jpg')->data(['test_date' => 1627008264])->save();
        Asset::find('test::e.jpg')->data(['test_date' => null])->save();

        $assets = $this->container->queryAssets()->whereDay('test_date', 15)->get();

        $this->assertCount(2, $assets);
        $this->assertEquals(['a', 'c'], $assets->map->filename()->all());

        $assets = $this->container->queryAssets()->whereDay('test_date', '<', 15)->get();

        $this->assertCount(1, $assets);
        $this->assertEquals(['b'], $assets->map->filename()->all());
    }

    /** @test **/
    public function assets_are_found_using_where_year()
    {
        Asset::find('test::a.jpg')->data(['test_date' => 1637008264])->save();
        Asset::find('test::b.txt')->data(['test_date' => '2021-11-14 09:00:00'])->save();
        Asset::find('test::c.txt')->data(['test_date' => '2021-11-15'])->save();
        Asset::find('test::d.jpg')->data(['test_date' => 1600008264])->save();
        Asset::find('test::e.jpg')->data(['test_date' => null])->save();

        $assets = $this->container->queryAssets()->whereYear('test_date', 2021)->get();

        $this->assertCount(3, $assets);
        $this->assertEquals(['a', 'b', 'c'], $assets->map->filename()->all());

        $assets = $this->container->queryAssets()->whereYear('test_date', '<', 2021)->get();

        $this->assertCount(1, $assets);
        $this->assertEquals(['d'], $assets->map->filename()->all());
    }

    /** @test **/
    public function assets_are_found_using_where_time()
    {
        Asset::find('test::a.jpg')->data(['test_date' => 1637008264])->save();
        Asset::find('test::b.txt')->data(['test_date' => '2021-11-14 09:00:00'])->save();
        Asset::find('test::c.txt')->data(['test_date' => '2021-11-15'])->save();
        Asset::find('test::d.jpg')->data(['test_date' => 1600008264])->save();
        Asset::find('test::e.jpg')->data(['test_date' => null])->save();

        $assets = $this->container->queryAssets()->whereTime('test_date', '09:00')->get();

        $this->assertCount(1, $assets);
        $this->assertEquals(['b'], $assets->map->filename()->all());

        $assets = $this->container->queryAssets()->whereTime('test_date', '>', '09:00')->get();

        $this->assertCount(2, $assets);
        $this->assertEquals(['a', 'd'], $assets->map->filename()->all());
    }

    public function assets_are_found_using_where_null()
    {
        Asset::find('test::a.jpg')->data(['text' => 'Text 1'])->save();
        Asset::find('test::b.txt')->data(['text' => 'Text 2'])->save();
        Asset::find('test::c.txt')->data([])->save();
        Asset::find('test::d.jpg')->data(['text' => 'Text 4'])->save();
        Asset::find('test::e.jpg')->data([])->save();
        Asset::find('test::f.jpg')->data([])->save();

        $assets = $this->container->queryAssets()->whereNull('text')->get();

        $this->assertCount(3, $assets);
        $this->assertEquals(['c', 'e', 'f'], $assets->map->filename()->all());
    }

    /** @test **/
    public function assets_are_found_using_where_not_null()
    {
        Asset::find('test::a.jpg')->data(['text' => 'Text 1'])->save();
        Asset::find('test::b.txt')->data(['text' => 'Text 2'])->save();
        Asset::find('test::c.txt')->data([])->save();
        Asset::find('test::d.jpg')->data(['text' => 'Text 4'])->save();
        Asset::find('test::e.jpg')->data([])->save();
        Asset::find('test::f.jpg')->data([])->save();

        $assets = $this->container->queryAssets()->whereNotNull('text')->get();

        $this->assertCount(3, $assets);
        $this->assertEquals(['a', 'b', 'd'], $assets->map->filename()->all());
    }

    /** @test **/
    public function assets_are_found_using_or_where_null()
    {
        Asset::find('test::a.jpg')->data(['text' => 'Text 1', 'content' => 'Content 1'])->save();
        Asset::find('test::b.txt')->data(['text' => 'Text 2'])->save();
        Asset::find('test::c.txt')->data(['content' => 'Content 1'])->save();
        Asset::find('test::d.jpg')->data(['text' => 'Text 4'])->save();
        Asset::find('test::e.jpg')->data([])->save();
        Asset::find('test::f.jpg')->data([])->save();

        $assets = $this->container->queryAssets()->whereNull('text')->orWhereNull('content')->get();

        $this->assertCount(5, $assets);
        $this->assertEquals(['c', 'e', 'f', 'b', 'd'], $assets->map->filename()->all());
    }

    /** @test **/
    public function assets_are_found_using_or_where_not_null()
    {
        Asset::find('test::a.jpg')->data(['text' => 'Text 1', 'content' => 'Content 1'])->save();
        Asset::find('test::b.txt')->data(['text' => 'Text 2'])->save();
        Asset::find('test::c.txt')->data(['content' => 'Content 1'])->save();
        Asset::find('test::d.jpg')->data(['text' => 'Text 4'])->save();
        Asset::find('test::e.jpg')->data([])->save();
        Asset::find('test::f.jpg')->data([])->save();

        $assets = $this->container->queryAssets()->whereNotNull('content')->orWhereNotNull('text')->get();

        $this->assertCount(4, $assets);
        $this->assertEquals(['a', 'c', 'b', 'd'], $assets->map->filename()->all());
    }

    /** @test **/
    public function assets_are_found_using_nested_where()
    {
        $assets = $this->container->queryAssets()
            ->where(function ($query) {
                $query->where('filename', 'a');
            })
            ->orWhere(function ($query) {
                $query->where('filename', 'c')->orWhere('filename', 'd');
            })
            ->orWhere('filename', 'f')
            ->get();

        $this->assertCount(4, $assets);
        $this->assertEquals(['a', 'c', 'd', 'f'], $assets->map->filename()->all());
    }

    /** @test **/
    public function assets_are_found_using_nested_where_in()
    {
        $assets = $this->container->queryAssets()
            ->where(function ($query) {
                $query->whereIn('filename', ['a', 'b']);
            })
            ->orWhere(function ($query) {
                $query->whereIn('filename', ['a', 'd'])
                    ->orWhereIn('extension', ['txt']);
            })
            ->orWhereIn('filename', ['f'])
            ->get();

        $this->assertCount(5, $assets);
        $this->assertEquals(['a', 'b', 'd', 'c', 'f'], $assets->map->filename()->all());
    }
}
