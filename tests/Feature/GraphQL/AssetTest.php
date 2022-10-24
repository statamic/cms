<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blueprint;
use Statamic\Facades\GraphQL;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class AssetTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use EnablesQueries;

    protected $enabledQueries = ['assets'];

    protected function setUp(): void
    {
        parent::setUp();

        BlueprintRepository::partialMock();
    }

    /**
     * @test
     * @environment-setup disableQueries
     **/
    public function query_only_works_if_enabled()
    {
        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{asset}'])
            ->assertSee('Cannot query field \"asset\" on type \"Query\"', false);
    }

    /** @test */
    public function it_queries_an_asset_by_id()
    {
        Carbon::setTestNow(Carbon::parse('2012-01-02 5:00pm'));
        Storage::fake('test', ['url' => '/assets']);
        $file = UploadedFile::fake()->image('image.jpg', 30, 60); // creates a 723 byte image
        Storage::disk('test')->putFileAs('sub', $file, 'image.jpg');
        $realFilePath = Storage::disk('test')->path('sub/image.jpg');
        touch($realFilePath, Carbon::now()->subMinutes(3)->timestamp);
        tap($container = AssetContainer::make('test')->disk('test')->title('Test'))->save();
        $container->makeAsset('sub/image.jpg')->data(['potato' => 'baked'])->save();
        $blueprint = Blueprint::makeFromFields(['potato' => ['type' => 'text']]);
        BlueprintRepository::shouldReceive('find')->with('assets/test')->andReturn($blueprint);

        $query = <<<'GQL'
{
    asset(id: "test::sub/image.jpg") {
        id
        path
        extension
        is_audio
        is_image
        is_video
        edit_url
        container {
            title
            handle
        }
        folder
        url
        permalink
        size
        size_bytes
        size_kilobytes
        size_megabytes
        size_gigabytes
        size_b
        size_kb
        size_mb
        size_gb
        last_modified
        focus_css
        height
        width
        orientation
        ratio
        ... on Asset_Test {
            potato
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'asset' => [
                    'id' => 'test::sub/image.jpg',
                    'path' => 'sub/image.jpg',
                    'extension' => 'jpg',
                    'is_audio' => false,
                    'is_image' => true,
                    'is_video' => false,
                    'edit_url' => 'http://localhost/cp/assets/browse/test/sub/image.jpg/edit',
                    'container' => ['title' => 'Test', 'handle' => 'test'],
                    'folder' => 'sub',
                    'url' => '/assets/sub/image.jpg',
                    'permalink' => 'http://localhost/assets/sub/image.jpg',
                    'size' => '723 B',
                    'size_bytes' => 723,
                    'size_kilobytes' => 0.71,
                    'size_megabytes' => 0,
                    'size_gigabytes' => 0,
                    'size_b' => 723,
                    'size_kb' => 0.71,
                    'size_mb' => 0,
                    'size_gb' => 0,
                    'last_modified' => '2012-01-02 16:57:00',
                    'focus_css' => '50% 50%',
                    'height' => 60,
                    'width' => 30,
                    'orientation' => 'portrait',
                    'ratio' => 0.5,
                    'potato' => 'baked',
                ],
            ]]);
    }

    /** @test */
    public function it_queries_an_asset_by_container_and_path()
    {
        Storage::fake('test', ['url' => '/assets']);
        Storage::disk('test')->put('a.txt', '');
        Storage::disk('test')->put('b.txt', '');
        Storage::disk('test')->put('c.txt', '');
        AssetContainer::make('test')->disk('test')->save();

        $query = <<<'GQL'
{
    asset(container: "test", path: "b.txt") {
        path
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'asset' => [
                    'path' => 'b.txt',
                ],
            ]]);
    }

    /** @test */
    public function it_can_add_custom_fields_to_interface()
    {
        GraphQL::addField('AssetInterface', 'one', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'first';
                },
            ];
        });

        GraphQL::addField('AssetInterface', 'two', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'second';
                },
            ];
        });

        GraphQL::addField('AssetInterface', 'extension', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'the overridden extension';
                },
            ];
        });

        Storage::fake('test', ['url' => '/assets']);
        Storage::disk('test')->put('a.txt', '');
        AssetContainer::make('test')->disk('test')->save();

        $query = <<<'GQL'
{
    asset(id: "test::a.txt") {
        path
        one
        two
        extension
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'asset' => [
                    'path' => 'a.txt',
                    'one' => 'first',
                    'two' => 'second',
                    'extension' => 'the overridden extension',
                ],
            ]]);
    }

    /** @test */
    public function it_can_add_custom_fields_to_an_implementation()
    {
        GraphQL::addField('Asset_Test', 'one', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'first';
                },
            ];
        });

        GraphQL::addField('Asset_Test', 'extension', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'the overridden extension';
                },
            ];
        });

        Storage::fake('test', ['url' => '/assets']);
        Storage::disk('test')->put('a.txt', '');
        AssetContainer::make('test')->disk('test')->save();

        $query = <<<'GQL'
{
    asset(id: "test::a.txt") {
        path
        ... on Asset_Test {
            one
            extension
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'asset' => [
                    'path' => 'a.txt',
                    'one' => 'first',
                    'extension' => 'the overridden extension',
                ],
            ]]);
    }

    /** @test */
    public function adding_custom_field_to_an_implementation_does_not_add_it_to_the_interface()
    {
        GraphQL::addField('Asset_Test', 'one', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'first';
                },
            ];
        });

        Storage::fake('test', ['url' => '/assets']);
        Storage::disk('test')->put('a.txt', '');
        AssetContainer::make('test')->disk('test')->save();

        $query = <<<'GQL'
{
    asset(id: "test::a.txt") {
        path
        one
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertJson(['errors' => [[
                'message' => 'Cannot query field "one" on type "AssetInterface". Did you mean to use an inline fragment on "Asset_Test"?',
            ]]]);
    }
}
