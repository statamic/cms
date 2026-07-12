<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\UserGroup;
use Tests\FakesUserGroups;
use Tests\Feature\GraphQL\EnablesQueries;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class UserGroupsFieldtypeTest extends TestCase
{
    use EnablesQueries;
    use FakesUserGroups;
    use PreventSavingStacheItemsToDisk;

    protected $enabledQueries = ['collections'];

    public function setUp(): void
    {
        parent::setUp();
        BlueprintRepository::partialMock();

        $this->setTestUserGroups([
            'admin' => UserGroup::make()->title('Administrators'),
            'editors' => UserGroup::make()->title('Content Editors'),
        ]);
    }

    #[Test]
    public function it_gets_multiple_groups()
    {
        $article = Blueprint::makeFromFields([
            'related_groups' => ['type' => 'user_groups'],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'related_groups' => ['admin', 'editors'],
        ])->create();

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            related_groups {
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
                    'related_groups' => [
                        ['handle' => 'admin', 'title' => 'Administrators'],
                        ['handle' => 'editors', 'title' => 'Content Editors'],
                    ],
                ],
            ]]);
    }

    #[Test]
    public function it_gets_single_collection()
    {
        $article = Blueprint::makeFromFields([
            'related_group' => ['type' => 'user_groups', 'max_items' => 1],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'related_group' => 'admin',
        ])->create();

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            related_group {
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
                    'related_group' => [
                        'handle' => 'admin',
                        'title' => 'Administrators',
                    ],
                ],
            ]]);
    }
}
