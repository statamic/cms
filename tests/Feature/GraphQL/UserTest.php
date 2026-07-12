<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\API\ResourceAuthorizer;
use Facades\Statamic\Fields\BlueprintRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\GraphQL;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class UserTest extends TestCase
{
    use CreatesQueryableTestEntries;
    use EnablesQueries;
    use PreventSavingStacheItemsToDisk;

    protected $enabledQueries = ['users'];

    private function createUsers()
    {
        User::make()->id('1')->email('a@example.com')->set('name', 'Carmen Sandiego')->save();
        User::make()->id('2')->email('b@example.com')->set('name', 'Edgar Allen Poe')->save();
        User::make()->id('3')->email('c@example.com')->set('name', 'Burt Wonderstone')->save();
        User::make()->id('4')->email('d@example.com')->set('name', 'Gary Busey')->save();
        User::make()->id('5')->email('e@example.com')->set('name', 'Dolores Mulva')->save();
        User::make()->id('6')->email('f@example.com')->set('name', 'Alan Alda')->save();
        User::make()->id('7')->email('g@example.com')->set('name', 'Fred Armisen')->save();
    }

    #[Test]
    public function query_only_works_if_enabled()
    {
        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'users')->andReturnFalse()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'users')->never();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{user}'])
            ->assertSee('Cannot query field \"user\" on type \"Query\"', false);
    }

    #[Test]
    public function it_queries_a_user_by_id()
    {
        $this->createUsers();

        $query = <<<'GQL'
{
    user(id: "3") {
        id
        email
        name
        initials
        edit_url
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'users')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'users')->never();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'user' => [
                    'id' => '3',
                    'email' => 'c@example.com',
                    'name' => 'Burt Wonderstone',
                    'initials' => 'BW',
                    'edit_url' => 'http://localhost/cp/users/3/edit',
                ],
            ]]);
    }

    #[Test]
    public function it_queries_a_user_by_email()
    {
        $this->createUsers();

        $query = <<<'GQL'
{
    user(email: "c@example.com") {
        id
        email
        name
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'user' => [
                    'id' => '3',
                    'email' => 'c@example.com',
                    'name' => 'Burt Wonderstone',
                ],
            ]]);
    }

    #[Test]
    public function it_can_add_custom_fields()
    {
        $this->createUsers();

        GraphQL::addField('User', 'one', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'first';
                },
            ];
        });

        GraphQL::addField('User', 'two', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'second';
                },
            ];
        });

        GraphQL::addField('User', 'name', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($a) {
                    return 'the overridden name';
                },
            ];
        });

        $query = <<<'GQL'
{
    user(id: "3") {
        id
        one
        two
        name
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'user' => [
                    'id' => '3',
                    'one' => 'first',
                    'two' => 'second',
                    'name' => 'the overridden name',
                ],
            ]]);
    }

    #[Test]
    public function it_can_register_custom_types()
    {
        $this->createUsers();

        $blueprint = Blueprint::makeFromFields([
            'grid_field' => [
                'type' => 'grid',
                'fields' => [
                    ['handle' => 'test', 'field' => ['type' => 'text']],
                ],
            ],
        ]);
        BlueprintRepository::shouldReceive('find')->with('user')->andReturn($blueprint);

        User::find(3)->set('grid_field', [['test' => 'Hulk Hogan']])->save();

        $query = <<<'GQL'
{
    user(id: "3") {
        id
        email
        name
        initials
        edit_url
        grid_field {
            test
        }
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'users')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'users')->never();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'user' => [
                    'id' => '3',
                    'email' => 'c@example.com',
                    'name' => 'Burt Wonderstone',
                    'initials' => 'BW',
                    'edit_url' => 'http://localhost/cp/users/3/edit',
                    'grid_field' => [['test' => 'Hulk Hogan']],
                ],
            ]]);
    }
}
