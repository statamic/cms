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

    /** @test **/
    public function assets_are_found_using_where_between()
    {
        Asset::find('test::a.jpg')->data(['number_field' => 8])->save();
        Asset::find('test::b.txt')->data(['number_field' => 9])->save();
        Asset::find('test::c.txt')->data(['number_field' => 10])->save();
        Asset::find('test::d.jpg')->data(['number_field' => 11])->save();
        Asset::find('test::e.jpg')->data(['number_field' => 12])->save();
        Asset::find('test::f.jpg')->data([])->save();

        $assets = $this->container->queryAssets()->whereBetween('number_field', [9, 11])->get();

        $this->assertCount(3, $assets);
        $this->assertEquals(['b', 'c', 'd'], $assets->map->filename()->all());
    }

    /** @test **/
    public function assets_are_found_using_where_not_between()
    {
        Asset::find('test::a.jpg')->data(['number_field' => 8])->save();
        Asset::find('test::b.txt')->data(['number_field' => 9])->save();
        Asset::find('test::c.txt')->data(['number_field' => 10])->save();
        Asset::find('test::d.jpg')->data(['number_field' => 11])->save();
        Asset::find('test::e.jpg')->data(['number_field' => 12])->save();
        Asset::find('test::f.jpg')->data([])->save();

        $assets = $this->container->queryAssets()->whereNotBetween('number_field', [9, 11])->get();

        $this->assertCount(3, $assets);
        $this->assertEquals(['a', 'e', 'f'], $assets->map->filename()->all());
    }

    /** @test **/
    public function assets_are_found_using_or_where_between()
    {
        Asset::find('test::a.jpg')->data(['number_field' => 8])->save();
        Asset::find('test::b.txt')->data(['number_field' => 9])->save();
        Asset::find('test::c.txt')->data(['number_field' => 10])->save();
        Asset::find('test::d.jpg')->data(['number_field' => 11])->save();
        Asset::find('test::e.jpg')->data(['number_field' => 12])->save();
        Asset::find('test::f.jpg')->data([])->save();

        $assets = $this->container->queryAssets()->whereBetween('number_field', [9, 10])->orWhereBetween('number_field', [11, 12])->get();

        $this->assertCount(4, $assets);
        $this->assertEquals(['b', 'c', 'd', 'e'], $assets->map->filename()->all());
    }

    /** @test **/
    public function assets_are_found_using_or_where_not_between()
    {
        Asset::find('test::a.jpg')->data(['text' => 'a', 'number_field' => 8])->save();
        Asset::find('test::b.txt')->data(['text' => 'b', 'number_field' => 9])->save();
        Asset::find('test::c.txt')->data(['text' => 'c', 'number_field' => 10])->save();
        Asset::find('test::d.jpg')->data(['text' => 'd', 'number_field' => 11])->save();
        Asset::find('test::e.jpg')->data(['text' => 'e', 'number_field' => 12])->save();
        Asset::find('test::f.jpg')->data([])->save();

        $assets = $this->container->queryAssets()->where('text', 'e')->orWhereNotBetween('number_field', [10, 12])->get();

        $this->assertCount(4, $assets);
        $this->assertEquals(['e', 'a', 'b', 'f'], $assets->map->filename()->all());
    }

    /** @test **/
    public function assets_are_found_using_array_of_wheres()
    {
        $assets = $this->container->queryAssets()
            ->where([
                'filename' => 'a',
                ['extension', 'jpg'],
            ])
            ->get();

        $this->assertCount(1, $assets);
        $this->assertEquals(['a'], $assets->map->filename()->all());
    }

    /** @test **/
    public function results_are_found_using_where_with_json_value()
    {
        Asset::find('test::a.jpg')->data(['text' => 'Text 1', 'content' => ['value' => 1]])->save();
        Asset::find('test::b.txt')->data(['text' => 'Text 2', 'content' => ['value' => 2]])->save();
        Asset::find('test::c.txt')->data(['content' => ['value' => 1]])->save();
        Asset::find('test::d.jpg')->data(['text' => 'Text 4'])->save();
        // the following two assets use scalars for the content field to test that they get successfully ignored.
        Asset::find('test::e.jpg')->data(['content' => 'string'])->save();
        Asset::find('test::f.jpg')->data(['content' => 123])->save();

        $assets = $this->container->queryAssets()->where('content->value', 1)->get();

        $this->assertCount(2, $assets);
        $this->assertEquals(['a', 'c'], $assets->map->filename()->all());

        $assets = $this->container->queryAssets()->where('content->value', '!=', 1)->get();

        $this->assertCount(4, $assets);
        $this->assertEquals(['b', 'd', 'e', 'f'], $assets->map->filename()->all());
    }

    /** @test **/
    public function assets_are_found_using_where_column()
    {
        Asset::find('test::a.jpg')->data(['foo' => 'Post 1', 'other_foo' => 'Not Post 1'])->save();
        Asset::find('test::b.txt')->data(['foo' => 'Post 2', 'other_foo' => 'Not Post 2'])->save();
        Asset::find('test::c.txt')->data(['foo' => 'Post 3', 'other_foo' => 'Post 3'])->save();
        Asset::find('test::d.jpg')->data(['foo' => 'Post 4', 'other_foo' => 'Post 4'])->save();
        Asset::find('test::e.jpg')->data(['foo' => 'Post 5', 'other_foo' => 'Not Post 5'])->save();
        Asset::find('test::f.jpg')->data(['foo' => 'Post 6', 'other_foo' => 'Not Post 6'])->save();

        $entries = $this->container->queryAssets()->whereColumn('foo', 'other_foo')->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 3', 'Post 4'], $entries->map->foo->all());

        $entries = $this->container->queryAssets()->whereColumn('foo', '!=', 'other_foo')->get();

        $this->assertCount(4, $entries);
        $this->assertEquals(['Post 1', 'Post 2', 'Post 5', 'Post 6'], $entries->map->foo->all());
    }

    /** @test */
    public function it_can_get_assets_using_when()
    {
        $assets = $this->container->queryAssets()->when(true, function ($query) {
            $query->where('filename', 'a');
        })->get();

        $this->assertCount(1, $assets);
        $this->assertEquals(['a'], $assets->map->filename()->all());

        $assets = $this->container->queryAssets()->when(false, function ($query) {
            $query->where('filename', 'a');
        })->get();

        $this->assertCount(6, $assets);
        $this->assertEquals(['a', 'b', 'c', 'd', 'e', 'f'], $assets->map->filename()->all());
    }

    /** @test */
    public function it_can_get_assets_using_unless()
    {
        $assets = $this->container->queryAssets()->unless(true, function ($query) {
            $query->where('filename', 'a');
        })->get();

        $this->assertCount(6, $assets);
        $this->assertEquals(['a', 'b', 'c', 'd', 'e', 'f'], $assets->map->filename()->all());

        $assets = $this->container->queryAssets()->unless(false, function ($query) {
            $query->where('filename', 'a');
        })->get();

        $this->assertCount(1, $assets);
        $this->assertEquals(['a'], $assets->map->filename()->all());
    }

    /** @test */
    public function it_can_get_assets_using_tap()
    {
        $assets = $this->container->queryAssets()->tap(function ($query) {
            $query->where('filename', 'a');
        })->get();

        $this->assertCount(1, $assets);
        $this->assertEquals(['a'], $assets->map->filename()->all());
    }
}
