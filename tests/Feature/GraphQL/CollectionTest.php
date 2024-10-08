<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\API\ResourceAuthorizer;
use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\GraphQL;
use Statamic\Structures\CollectionStructure;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class CollectionTest extends TestCase
{
    use EnablesQueries;
    use PreventSavingStacheItemsToDisk;

    protected $enabledQueries = ['collections'];

    public function setUp(): void
    {
        parent::setUp();

        Collection::make('blog')->title('Blog Posts')->save();
        Collection::make('events')->title('Events')->save();
    }

    #[Test]
    public function query_only_works_if_enabled()
    {
        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'collections')->andReturnFalse()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'collections')->never();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{collection}'])
            ->assertSee('Cannot query field \"collection\" on type \"Query\"', false);
    }

    #[Test]
    public function it_queries_a_collection_by_handle()
    {
        $query = <<<'GQL'
{
    collection(handle: "events") {
        handle
        title
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'collections')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'collections')->andReturn(Collection::handles()->all())->once();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'collection' => [
                    'handle' => 'events',
                    'title' => 'Events',
                ],
            ]]);
    }

    #[Test]
    public function it_cannot_query_against_non_allowed_sub_resource()
    {
        $query = <<<'GQL'
{
    collection(handle: "events") {
        handle
        title
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'collections')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'collections')->andReturn(['blog'])->once();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertJson([
                'errors' => [[
                    'message' => 'validation',
                    'extensions' => [
                        'validation' => [
                            'handle' => ['Forbidden: events'],
                        ],
                    ],
                ]],
                'data' => [
                    'collection' => null,
                ],
            ]);
    }

    #[Test]
    public function it_queries_the_structure_and_its_tree()
    {
        // Start with fresh slate for this test so it's easier to mock things with one pages collection...
        Collection::all()->each->delete();

        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            'fr' => ['name' => 'french', 'locale' => 'fr_FR', 'url' => 'http://test.com/fr/'],
        ]);

        BlueprintRepository::partialMock();
        $blueprint = Blueprint::makeFromFields(['foo' => ['type' => 'text']])->setHandle('pages');
        BlueprintRepository::shouldReceive('in')->with('collections/pages')->andReturn(collect(['pages' => $blueprint]));

        $collection = Collection::make('pages')->title('Pages')->routes('{parent_uri}/{slug}')->sites(['en', 'fr']);
        $structure = (new CollectionStructure)->maxDepth(3)->expectsRoot(true);
        $collection->structure($structure)->save();

        EntryFactory::collection('pages')->id('home')->slug('home')->data(['title' => 'Home', 'foo' => 'bar'])->create();
        EntryFactory::collection('pages')->id('about')->slug('about')->data(['title' => 'About', 'foo' => 'baz'])->create();
        EntryFactory::collection('pages')->id('team')->slug('team')->data(['title' => 'Team'])->create();

        EntryFactory::collection('pages')->locale('fr')->id('fr-home')->slug('fr-home')->data(['title' => 'Fr Home'])->create();
        EntryFactory::collection('pages')->locale('fr')->id('fr-about')->slug('fr-about')->data(['title' => 'Fr About'])->create();
        EntryFactory::collection('pages')->locale('fr')->id('fr-team')->slug('fr-team')->data(['title' => 'Fr Team'])->create();

        $collection->structure()->in('en')->tree([
            ['entry' => 'home'],
            ['entry' => 'about', 'children' => [
                ['entry' => 'team'],
            ]],
        ])->save();

        $collection->structure()->in('fr')->tree([
            ['entry' => 'fr-home'],
            ['entry' => 'fr-about', 'children' => [
                ['entry' => 'fr-team'],
            ]],
        ])->save();

        $query = <<<'GQL'
{
    collection(handle: "pages") {
        structure {
            max_depth
            expects_root
            englishTree: tree {
                depth
                entry {
                    id
                    title
                    url
                    ... on Entry_Pages_Pages {
                        foo
                    }
                }
                children {
                    depth
                    entry {
                        id
                        title
                        url
                        ... on Entry_Pages_Pages {
                            foo
                        }
                    }
                }
            }
            frenchTree: tree(site: "fr") {
                depth
                entry {
                    id
                    title
                    url
                    ... on Entry_Pages_Pages {
                        foo
                    }
                }
                children {
                    depth
                    entry {
                        id
                        title
                        url
                        ... on Entry_Pages_Pages {
                            foo
                        }
                    }
                }
            }
        }
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'collections')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'collections')->andReturn(['pages'])->once();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'collection' => [
                    'structure' => [
                        'max_depth' => 3,
                        'expects_root' => true,
                        'englishTree' => [
                            [
                                'depth' => 1,
                                'entry' => [
                                    'id' => 'home',
                                    'title' => 'Home',
                                    'url' => '/',
                                    'foo' => 'bar',
                                ],
                                'children' => [],
                            ],
                            [
                                'depth' => 1,
                                'entry' => [
                                    'id' => 'about',
                                    'title' => 'About',
                                    'url' => '/about',
                                    'foo' => 'baz',
                                ],
                                'children' => [
                                    [
                                        'depth' => 2,
                                        'entry' => [
                                            'id' => 'team',
                                            'title' => 'Team',
                                            'url' => '/about/team',
                                            'foo' => null,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'frenchTree' => [
                            [
                                'depth' => 1,
                                'entry' => [
                                    'id' => 'fr-home',
                                    'title' => 'Fr Home',
                                    'url' => '/fr',
                                    'foo' => null,
                                ],
                                'children' => [],
                            ],
                            [
                                'depth' => 1,
                                'entry' => [
                                    'id' => 'fr-about',
                                    'title' => 'Fr About',
                                    'url' => '/fr/fr-about',
                                    'foo' => null,
                                ],
                                'children' => [
                                    [
                                        'depth' => 2,
                                        'entry' => [
                                            'id' => 'fr-team',
                                            'title' => 'Fr Team',
                                            'url' => '/fr/fr-about/fr-team',
                                            'foo' => null,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]]);
    }

    #[Test]
    public function it_can_add_custom_fields()
    {
        GraphQL::addField('Collection', 'custom', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'the custom value';
                },
            ];
        });

        $query = <<<'GQL'
{
    collection(handle: "blog") {
        handle
        custom
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'collection' => [
                    'handle' => 'blog',
                    'custom' => 'the custom value',
                ],
            ]]);
    }
}
