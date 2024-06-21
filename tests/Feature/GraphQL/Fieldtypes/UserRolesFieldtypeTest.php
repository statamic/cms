<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Role;
use Tests\FakesRoles;
use Tests\Feature\GraphQL\EnablesQueries;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class UserRolesFieldtypeTest extends TestCase
{
    use EnablesQueries;
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    protected $enabledQueries = ['collections'];

    public function setUp(): void
    {
        parent::setUp();
        BlueprintRepository::partialMock();

        $this->setTestRoles([
            'admin' => Role::make()->title('Administrators'),
            'editors' => Role::make()->title('Content Editors'),
        ]);
    }

    #[Test]
    public function it_gets_multiple_roles()
    {
        $article = Blueprint::makeFromFields([
            'related_roles' => ['type' => 'user_roles'],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'related_roles' => ['admin', 'editors'],
        ])->create();

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

    #[Test]
    public function it_gets_single_role()
    {
        $article = Blueprint::makeFromFields([
            'related_role' => ['type' => 'user_roles', 'max_items' => 1],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'related_role' => 'admin',
        ])->create();

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
