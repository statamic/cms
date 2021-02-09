<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Role;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class UserRolesFieldtypeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use FakesRoles;

    public function setUp(): void
    {
        parent::setUp();
        BlueprintRepository::partialMock();

        $this->setTestRoles([
            'admin' => Role::make()->title('Administrators'),
            'editors' => Role::make()->title('Content Editors'),
        ]);
    }

    /** @test */
    public function it_gets_multiple_roles()
    {
        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'related_roles' => ['admin', 'editors'],
        ])->create();

        $article = Blueprint::makeFromFields([
            'related_roles' => ['type' => 'user_roles'],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            related_roles {
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
                    'related_roles' => [
                        ['handle' => 'admin', 'title' => 'Administrators'],
                        ['handle' => 'editors', 'title' => 'Content Editors'],
                    ],
                ],
            ]]);
    }

    /** @test */
    public function it_gets_single_role()
    {
        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'related_role' => 'admin',
        ])->create();

        $article = Blueprint::makeFromFields([
            'related_role' => ['type' => 'user_roles', 'max_items' => 1],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            related_role {
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
                    'related_role' => [
                        'handle' => 'admin',
                        'title' => 'Administrators',
                    ],
                ],
            ]]);
    }
}
