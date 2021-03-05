<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Blueprint;
use Statamic\Facades\User;
use Tests\Feature\GraphQL\EnablesQueries;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class UsersFieldtypeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use EnablesQueries;

    protected $enabledQueries = ['collections'];

    public function setUp(): void
    {
        parent::setUp();
        BlueprintRepository::partialMock();
    }

    /** @test */
    public function it_gets_multiple_users()
    {
        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'related_users' => [2, 3],
        ])->create();
        User::make()->id(2)->set('name', 'Burt')->save();
        User::make()->id(3)->set('name', 'Janet')->save();

        $article = Blueprint::makeFromFields([
            'related_users' => ['type' => 'users'],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            related_users {
                id
                name
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
                    'related_users' => [
                        ['id' => '2', 'name' => 'Burt'],
                        ['id' => '3', 'name' => 'Janet'],
                    ],
                ],
            ]]);
    }

    /** @test */
    public function it_gets_single_user()
    {
        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'related_user' => 2,
        ])->create();
        User::make()->id(2)->set('name', 'Burt')->save();

        $article = Blueprint::makeFromFields([
            'related_user' => ['type' => 'users', 'max_items' => 1],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            related_user {
                id
                name
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
                    'related_user' => [
                        'id' => '2',
                        'name' => 'Burt',
                    ],
                ],
            ]]);
    }
}
