<?php

namespace Tests\Data\Assets;

use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blueprint;
use Statamic\Query\Scopes\Scope;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class AssetQueryBuilderTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $container;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('test', ['url' => '/assets']);

        Storage::disk('test')->put('a.jpg', '');
        Storage::disk('test')->put('b.txt', '');
        Storage::disk('test')->put('c.txt', '');
        Storage::disk('test')->put('d.jpg', '');
        Storage::disk('test')->put('e.jpg', '');
        Storage::disk('test')->put('f.jpg', '');
        $this->container = tap(AssetContainer::make('test')->disk('test'))->save();
    }

    #[Test]
    public function it_can_get_assets()
    {
        $assets = $this->container->queryAssets()->get();

        $this->assertCount(6, $assets);
        $this->assertEquals(['a', 'b', 'c', 'd', 'e', 'f'], $assets->map->filename()->all());
    }

    #[Test]
    public function assets_are_found_using_or_where()
    {
        $assets = $this->container->queryAssets()->where('filename', 'a')->orWhere('filename', 'c')->get();

        $this->assertCount(2, $assets);
        $this->assertEquals(['a', 'c'], $assets->map->filename()->all());
    }

    #[Test]
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

    #[Test]
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

    private function createWhereDateTestAssets()
    {
        $blueprint = Blueprint::makeFromFields(['test_date' => ['type' => 'date', 'time_enabled' => true]]);
        Blueprint::shouldReceive('find')->with('assets/test')->andReturn($blueprint);

        Asset::find('test::a.jpg')->data(['test_date' => '2021-11-15 20:31:04'])->save();
        Asset::find('test::b.txt')->data(['test_date' => '2021-11-14 09:00:00'])->save();
        Asset::find('test::c.txt')->data(['test_date' => '2021-11-15 00:00:00'])->save();
        Asset::find('test::d.jpg')->data(['test_date' => '2020-09-13 14:44:24'])->save();
        Asset::find('test::e.jpg')->data(['test_date' => null])->save();
    }

    #[Test]
    public function assets_are_found_using_where_date()
    {
        $this->createWhereDateTestAssets();

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

    #[Test]
    public function assets_are_found_using_where_month()
    {
        $this->createWhereDateTestAssets();

        $assets = $this->container->queryAssets()->whereMonth('test_date', 11)->get();

        $this->assertCount(3, $assets);
        $this->assertEquals(['a', 'b', 'c'], $assets->map->filename()->all());

        $assets = $this->container->queryAssets()->whereMonth('test_date', '<', 11)->get();

        $this->assertCount(1, $assets);
        $this->assertEquals(['d'], $assets->map->filename()->all());
    }

    #[Test]
    public function assets_are_found_using_where_day()
    {
        $this->createWhereDateTestAssets();

        $assets = $this->container->queryAssets()->whereDay('test_date', 15)->get();

        $this->assertCount(2, $assets);
        $this->assertEquals(['a', 'c'], $assets->map->filename()->all());

        $assets = $this->container->queryAssets()->whereDay('test_date', '<', 15)->get();

        $this->assertCount(2, $assets);
        $this->assertEquals(['b', 'd'], $assets->map->filename()->all());
    }

    #[Test]
    public function assets_are_found_using_where_year()
    {
        $this->createWhereDateTestAssets();

        $assets = $this->container->queryAssets()->whereYear('test_date', 2021)->get();

        $this->assertCount(3, $assets);
        $this->assertEquals(['a', 'b', 'c'], $assets->map->filename()->all());

        $assets = $this->container->queryAssets()->whereYear('test_date', '<', 2021)->get();

        $this->assertCount(1, $assets);
        $this->assertEquals(['d'], $assets->map->filename()->all());
    }

    #[Test]
    public function assets_are_found_using_where_time()
    {
        $this->createWhereDateTestAssets();

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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function assets_are_found_using_where_json_contains()
    {
        Asset::find('test::a.jpg')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->save();
        Asset::find('test::b.txt')->data(['test_taxonomy' => ['taxonomy-3']])->save();
        Asset::find('test::c.txt')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->save();
        Asset::find('test::d.jpg')->data(['test_taxonomy' => ['taxonomy-3', 'taxonomy-4']])->save();
        Asset::find('test::e.jpg')->data(['test_taxonomy' => ['taxonomy-5']])->save();

        $assets = $this->container->queryAssets()->whereJsonContains('test_taxonomy', ['taxonomy-1', 'taxonomy-5'])->get();

        $this->assertCount(3, $assets);
        $this->assertEquals(['a', 'c', 'e'], $assets->map->filename()->all());

        $assets = $this->container->queryAssets()->whereJsonContains('test_taxonomy', 'taxonomy-1')->get();

        $this->assertCount(2, $assets);
        $this->assertEquals(['a', 'c'], $assets->map->filename()->all());
    }

    #[Test]
    public function assets_are_found_using_where_json_doesnt_contain()
    {
        Asset::find('test::a.jpg')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->save();
        Asset::find('test::b.txt')->data(['test_taxonomy' => ['taxonomy-3']])->save();
        Asset::find('test::c.txt')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->save();
        Asset::find('test::d.jpg')->data(['test_taxonomy' => ['taxonomy-3', 'taxonomy-4']])->save();
        Asset::find('test::e.jpg')->data(['test_taxonomy' => ['taxonomy-5']])->save();
        Asset::find('test::f.jpg')->data(['test_taxonomy' => ['taxonomy-1']])->save();

        $assets = $this->container->queryAssets()->whereJsonDoesntContain('test_taxonomy', ['taxonomy-1'])->get();

        $this->assertCount(3, $assets);
        $this->assertEquals(['b', 'd', 'e'], $assets->map->filename()->all());

        $assets = $this->container->queryAssets()->whereJsonDoesntContain('test_taxonomy', 'taxonomy-1')->get();

        $this->assertCount(3, $assets);
        $this->assertEquals(['b', 'd', 'e'], $assets->map->filename()->all());
    }

    #[Test]
    public function assets_are_found_using_or_where_json_contains()
    {
        Asset::find('test::a.jpg')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->save();
        Asset::find('test::b.txt')->data(['test_taxonomy' => ['taxonomy-3']])->save();
        Asset::find('test::c.txt')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->save();
        Asset::find('test::d.jpg')->data(['test_taxonomy' => ['taxonomy-3', 'taxonomy-4']])->save();
        Asset::find('test::e.jpg')->data(['test_taxonomy' => ['taxonomy-5']])->save();

        $assets = $this->container->queryAssets()->whereJsonContains('test_taxonomy', ['taxonomy-1'])->orWhereJsonContains('test_taxonomy', ['taxonomy-5'])->get();

        $this->assertCount(3, $assets);
        $this->assertEquals(['a', 'c', 'e'], $assets->map->filename()->all());
    }

    #[Test]
    public function assets_are_found_using_or_where_json_doesnt_contain()
    {
        Asset::find('test::a.jpg')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->save();
        Asset::find('test::b.txt')->data(['test_taxonomy' => ['taxonomy-3']])->save();
        Asset::find('test::c.txt')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->save();
        Asset::find('test::d.jpg')->data(['test_taxonomy' => ['taxonomy-3', 'taxonomy-4']])->save();
        Asset::find('test::e.jpg')->data(['test_taxonomy' => ['taxonomy-5']])->save();
        Asset::find('test::f.jpg')->data(['test_taxonomy' => ['taxonomy-5']])->save();

        $assets = $this->container->queryAssets()->whereJsonContains('test_taxonomy', ['taxonomy-1'])->orWhereJsonDoesntContain('test_taxonomy', ['taxonomy-5'])->get();

        $this->assertCount(4, $assets);
        $this->assertEquals(['a', 'c', 'b', 'd'], $assets->map->filename()->all());
    }

    #[Test]
    public function assets_are_found_using_where_json_overlaps()
    {
        Asset::find('test::a.jpg')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->save();
        Asset::find('test::b.txt')->data(['test_taxonomy' => ['taxonomy-3']])->save();
        Asset::find('test::c.txt')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->save();
        Asset::find('test::d.jpg')->data(['test_taxonomy' => ['taxonomy-3', 'taxonomy-4']])->save();
        Asset::find('test::e.jpg')->data(['test_taxonomy' => ['taxonomy-5']])->save();

        $assets = $this->container->queryAssets()->whereJsonOverlaps('test_taxonomy', ['taxonomy-1', 'taxonomy-5'])->get();

        $this->assertCount(3, $assets);
        $this->assertEquals(['a', 'c', 'e'], $assets->map->filename()->all());

        $assets = $this->container->queryAssets()->whereJsonOverlaps('test_taxonomy', 'taxonomy-1')->get();

        $this->assertCount(2, $assets);
        $this->assertEquals(['a', 'c'], $assets->map->filename()->all());
    }

    #[Test]
    public function assets_are_found_using_where_json_doesnt_overlap()
    {
        Asset::find('test::a.jpg')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->save();
        Asset::find('test::b.txt')->data(['test_taxonomy' => ['taxonomy-3']])->save();
        Asset::find('test::c.txt')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->save();
        Asset::find('test::d.jpg')->data(['test_taxonomy' => ['taxonomy-3', 'taxonomy-4']])->save();
        Asset::find('test::e.jpg')->data(['test_taxonomy' => ['taxonomy-5']])->save();
        Asset::find('test::f.jpg')->data(['test_taxonomy' => ['taxonomy-1']])->save();

        $assets = $this->container->queryAssets()->whereJsonDoesntOverlap('test_taxonomy', ['taxonomy-1'])->get();

        $this->assertCount(3, $assets);
        $this->assertEquals(['b', 'd', 'e'], $assets->map->filename()->all());

        $assets = $this->container->queryAssets()->whereJsonDoesntOverlap('test_taxonomy', 'taxonomy-1')->get();

        $this->assertCount(3, $assets);
        $this->assertEquals(['b', 'd', 'e'], $assets->map->filename()->all());
    }

    #[Test]
    public function assets_are_found_using_or_where_json_overlaps()
    {
        Asset::find('test::a.jpg')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->save();
        Asset::find('test::b.txt')->data(['test_taxonomy' => ['taxonomy-3']])->save();
        Asset::find('test::c.txt')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->save();
        Asset::find('test::d.jpg')->data(['test_taxonomy' => ['taxonomy-3', 'taxonomy-4']])->save();
        Asset::find('test::e.jpg')->data(['test_taxonomy' => ['taxonomy-5']])->save();

        $assets = $this->container->queryAssets()->whereJsonOverlaps('test_taxonomy', ['taxonomy-1'])->orWhereJsonOverlaps('test_taxonomy', ['taxonomy-5'])->get();

        $this->assertCount(3, $assets);
        $this->assertEquals(['a', 'c', 'e'], $assets->map->filename()->all());
    }

    #[Test]
    public function assets_are_found_using_or_where_json_doesnt_overlap()
    {
        Asset::find('test::a.jpg')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->save();
        Asset::find('test::b.txt')->data(['test_taxonomy' => ['taxonomy-3']])->save();
        Asset::find('test::c.txt')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->save();
        Asset::find('test::d.jpg')->data(['test_taxonomy' => ['taxonomy-3', 'taxonomy-4']])->save();
        Asset::find('test::e.jpg')->data(['test_taxonomy' => ['taxonomy-5']])->save();
        Asset::find('test::f.jpg')->data(['test_taxonomy' => ['taxonomy-5']])->save();

        $assets = $this->container->queryAssets()->whereJsonOverlaps('test_taxonomy', ['taxonomy-1'])->orWhereJsonDoesntOverlap('test_taxonomy', ['taxonomy-5'])->get();

        $this->assertCount(4, $assets);
        $this->assertEquals(['a', 'c', 'b', 'd'], $assets->map->filename()->all());
    }

    #[Test]
    public function assets_are_found_using_where_json_length()
    {
        Asset::find('test::a.jpg')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->save();
        Asset::find('test::b.txt')->data(['test_taxonomy' => ['taxonomy-3']])->save();
        Asset::find('test::c.txt')->data(['test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->save();
        Asset::find('test::d.jpg')->data(['test_taxonomy' => ['taxonomy-3', 'taxonomy-4', 'taxonomy-5']])->save();
        Asset::find('test::e.jpg')->data(['test_taxonomy' => ['taxonomy-5']])->save();

        $assets = $this->container->queryAssets()->whereJsonLength('test_taxonomy', 1)->orWhereJsonLength('test_taxonomy', 3)->get();

        $this->assertCount(3, $assets);
        $this->assertEquals(['b', 'e', 'd'], $assets->map->filename()->all());
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function it_can_get_assets_using_tap()
    {
        $assets = $this->container->queryAssets()->tap(function ($query) {
            $query->where('filename', 'a');
        })->get();

        $this->assertCount(1, $assets);
        $this->assertEquals(['a'], $assets->map->filename()->all());
    }

    #[Test]
    public function assets_are_found_using_scopes()
    {
        CustomScope::register();
        Asset::allowQueryScope(CustomScope::class);
        Asset::allowQueryScope(CustomScope::class, 'whereCustom');

        $this->assertCount(1, $this->container->queryAssets()->customScope(['path' => 'a.jpg'])->get());
        $this->assertCount(1, $this->container->queryAssets()->whereCustom(['path' => 'a.jpg'])->get());
    }

    #[Test]
    public function assets_are_found_using_offset()
    {
        $query = $this->container->queryAssets()->limit(3);

        $this->assertEquals(['a.jpg', 'b.txt', 'c.txt'], $query->get()->map->path()->all());

        $this->assertEquals(['b.txt', 'c.txt', 'd.jpg'], $query->offset(1)->get()->map->path()->all());
    }

    #[Test]
    public function querying_doesnt_generate_meta_files_unnecessarily()
    {
        $this->assertEquals([
            'a.jpg',
            'b.txt',
            'c.txt',
            'd.jpg',
            'e.jpg',
            'f.jpg',
        ], collect(Storage::disk('test')->allFiles())->sort()->values()->all());

        $assets = $this->container->queryAssets()->get();

        $this->assertCount(6, $assets);
        $this->assertEquals(['a', 'b', 'c', 'd', 'e', 'f'], $assets->map->filename()->all());

        $this->assertEquals([
            'a.jpg',
            'b.txt',
            'c.txt',
            'd.jpg',
            'e.jpg',
            'f.jpg',
        ], collect(Storage::disk('test')->allFiles())->sort()->values()->all());
    }

    #[Test]
    public function querying_a_data_field_will_generate_meta_files()
    {
        Storage::disk('test')->put('.meta/b.txt.yaml', "data:\n  foo: bar");
        Storage::disk('test')->put('.meta/d.jpg.yaml', "data:\n  foo: bar");
        Storage::disk('test')->put('.meta/e.jpg.yaml', "data:\n  foo: notbar");

        $this->assertEquals([
            '.meta/b.txt.yaml',
            '.meta/d.jpg.yaml',
            '.meta/e.jpg.yaml',
            'a.jpg',
            'b.txt',
            'c.txt',
            'd.jpg',
            'e.jpg',
            'f.jpg',
        ], collect(Storage::disk('test')->allFiles())->sort()->values()->all());

        $assets = $this->container->queryAssets()->where('foo', 'bar')->get();

        $this->assertCount(2, $assets);
        $this->assertEquals(['b', 'd'], $assets->map->filename()->all());

        $this->assertEquals([
            '.meta/a.jpg.yaml',
            '.meta/b.txt.yaml',
            '.meta/c.txt.yaml',
            '.meta/d.jpg.yaml',
            '.meta/e.jpg.yaml',
            '.meta/f.jpg.yaml',
            'a.jpg',
            'b.txt',
            'c.txt',
            'd.jpg',
            'e.jpg',
            'f.jpg',
        ], collect(Storage::disk('test')->allFiles())->sort()->values()->all());
    }

    #[Test]
    public function values_can_be_plucked()
    {
        $this->assertEquals([
            'test::a.jpg' => 'a.jpg',
            'test::b.txt' => 'b.txt',
            'test::c.txt' => 'c.txt',
            'test::d.jpg' => 'd.jpg',
            'test::e.jpg' => 'e.jpg',
            'test::f.jpg' => 'f.jpg',
        ], $this->container->queryAssets()->pluck('path', 'id')->all());

        $this->assertEquals([
            'a.jpg',
            'b.txt',
            'c.txt',
            'd.jpg',
            'e.jpg',
            'f.jpg',
        ], $this->container->queryAssets()->pluck('path')->all());

        // Assert only queried values are plucked.
        $this->assertSame([
            'a.jpg',
            'd.jpg',
            'e.jpg',
            'f.jpg',
        ], $this->container->queryAssets()->where('extension', 'jpg')->pluck('path')->all());
    }
}

class CustomScope extends Scope
{
    public function apply($query, $params)
    {
        $query->where('path', $params['path']);
    }
}
