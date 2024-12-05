<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Tests\Feature\GraphQL\EnablesQueries;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class CollectionsFieldtypeTest extends TestCase
{
    use EnablesQueries;
    use PreventSavingStacheItemsToDisk;

    protected $enabledQueries = ['collections'];

    public function setUp(): void
    {
        parent::setUp();
        BlueprintRepository::partialMock();
        BlueprintRepository::shouldReceive('find')->with('default')->andReturn(Blueprint::make());
        Collection::make('pages')->title('Pages')->save();
        Collection::make('events')->title('Events')->save();
    }

    #[Test]
    public function it_gets_multiple_collections()
    {
        $article = Blueprint::makeFromFields([
            'related_collections' => ['type' => 'collections'],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'related_collections' => ['pages', 'events'],
        ])->create();

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            related_collections {
                handle
                title
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
                    'related_collections' => [
                        ['handle' => 'pages', 'title' => 'Pages'],
                        ['handle' => 'events', 'title' => 'Events'],
                    ],
                ],
            ]]);
    }

    #[Test]
    public function it_gets_single_collection()
    {
        $article = Blueprint::makeFromFields([
            'related_collection' => ['type' => 'collections', 'max_items' => 1],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'related_collection' => 'pages',
        ])->create();

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            related_collection {
                handle
                title
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
                    'related_collection' => [
                        'handle' => 'pages',
                        'title' => 'Pages',
                    ],
                ],
            ]]);
    }
}
