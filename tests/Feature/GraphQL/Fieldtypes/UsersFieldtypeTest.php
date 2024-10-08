<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\User;
use Tests\Feature\GraphQL\EnablesQueries;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class UsersFieldtypeTest extends TestCase
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
    public function it_gets_multiple_users()
    {
        $article = Blueprint::makeFromFields([
            'related_users' => ['type' => 'users'],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'related_users' => [2, 3],
        ])->create();
        User::make()->id(2)->set('name', 'Burt')->save();
        User::make()->id(3)->set('name', 'Janet')->save();

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

    #[Test]
    public function it_gets_single_user()
    {
        $article = Blueprint::makeFromFields([
            'related_user' => ['type' => 'users', 'max_items' => 1],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'related_user' => 2,
        ])->create();
        User::make()->id(2)->set('name', 'Burt')->save();

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
