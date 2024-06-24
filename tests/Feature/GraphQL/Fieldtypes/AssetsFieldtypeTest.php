<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blueprint;
use Tests\Feature\GraphQL\EnablesQueries;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class AssetsFieldtypeTest extends TestCase
{
    use EnablesQueries;
    use PreventSavingStacheItemsToDisk;

    protected $enabledQueries = ['collections'];

    public function setUp(): void
    {
        parent::setUp();
        BlueprintRepository::partialMock();
    }

    #[Test]
    public function it_gets_multiple_assets()
    {
        Storage::fake('test', ['url' => '/assets']);
        Storage::disk('test')->put('foo.txt', '');
        Storage::disk('test')->put('bar.txt', '');
        AssetContainer::make('assets')->disk('test')->save();

        $asset = Blueprint::makeFromFields(['alt' => ['type' => 'text']]);
        $article = Blueprint::makeFromFields(['images' => ['type' => 'assets']]);

        BlueprintRepository::shouldReceive('find')->with('assets/assets')->andReturn($asset);
        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'images' => ['foo.txt', 'bar.txt'],
        ])->create();

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            images {
                id
                path
            }
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'entry' => [
                    'title' => 'Main Post',
                    'images' => [
                        ['id' => 'assets::foo.txt', 'path' => 'foo.txt'],
                        ['id' => 'assets::bar.txt', 'path' => 'bar.txt'],
                    ],
                ],
            ]]);
    }

    #[Test]
    public function it_gets_single_asset()
    {
        Storage::fake('test', ['url' => '/assets']);
        Storage::disk('test')->put('foo.txt', '');
        Storage::disk('test')->put('bar.txt', '');
        AssetContainer::make('assets')->disk('test')->save();

        $asset = Blueprint::makeFromFields(['alt' => ['type' => 'text']]);
        $article = Blueprint::makeFromFields([
            'image' => ['type' => 'assets', 'max_files' => 1],
        ]);

        BlueprintRepository::shouldReceive('find')->with('assets/assets')->andReturn($asset);
        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'image' => 'foo.txt',
        ])->create();

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            image {
                id
                path
            }
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'entry' => [
                    'title' => 'Main Post',
                    'image' => [
                        'id' => 'assets::foo.txt',
                        'path' => 'foo.txt',
                    ],
                ],
            ]]);
    }
}
