<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use Statamic\Facades\Blueprint;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class UsersTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use EnablesQueries;

    protected $enabledQueries = ['users'];

    private function createUsers()
    {
        User::make()->id('1')->email('a@example.com')->set('name', 'Carmen')->save();
        User::make()->id('2')->email('b@example.com')->set('name', 'Edgar')->save();
        User::make()->id('3')->email('c@example.com')->set('name', 'Burt')->save();
        User::make()->id('4')->email('d@example.com')->set('name', 'Gary')->save();
        User::make()->id('5')->email('e@example.com')->set('name', 'Dolores')->save();
        User::make()->id('6')->email('f@example.com')->set('name', 'Alan')->save();
        User::make()->id('7')->email('g@example.com')->set('name', 'Fred')->save();
    }

    /**
     * @test
     *
     * @environment-setup disableQueries
     **/
    public function query_only_works_if_enabled()
    {
        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{users}'])
            ->assertSee('Cannot query field \"users\" on type \"Query\"', false);
    }

    /** @test */
    public function it_queries_users()
    {
        $this->createUsers();

        $query = <<<'GQL'
{
    users {
        data {
            id
            email
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['users' => ['data' => [
                ['id' => '1', 'email' => 'a@example.com'],
                ['id' => '2', 'email' => 'b@example.com'],
                ['id' => '3', 'email' => 'c@example.com'],
                ['id' => '4', 'email' => 'd@example.com'],
                ['id' => '5', 'email' => 'e@example.com'],
                ['id' => '6', 'email' => 'f@example.com'],
                ['id' => '7', 'email' => 'g@example.com'],
            ]]]]);
    }

    /** @test */
    public function it_paginates_users()
    {
        $this->createUsers();

        $query = <<<'GQL'
{
    users(limit: 2, page: 3) {
        total
        per_page
        current_page
        from
        to
        last_page
        has_more_pages
        data {
            id
            email
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['users' => [
                'total' => 7,
                'per_page' => 2,
                'current_page' => 3,
                'from' => 5,
                'to' => 6,
                'last_page' => 4,
                'has_more_pages' => true,
                'data' => [
                    ['id' => '5', 'email' => 'e@example.com'],
                    ['id' => '6', 'email' => 'f@example.com'],
                ],
            ]]]);
    }

    /** @test */
    public function it_queries_blueprint_specific_fields()
    {
        User::make()->id('1')->email('a@example.com')->set('foo', 'bar')->save();
        $blueprint = Blueprint::makeFromFields(['foo' => ['type' => 'text']]);
        BlueprintRepository::shouldReceive('find')->with('user')->andReturn($blueprint);

        $query = <<<'GQL'
{
    users {
        data {
            id
            email
            foo
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['users' => ['data' => [
                [
                    'id' => '1',
                    'email' => 'a@example.com',
                    'foo' => 'bar',
                ],
            ]]]]);
    }

    /** @test */
    public function it_filters_users()
    {
        $this->createUsers();
        User::find('3')->set('bio', 'That was so rad!')->save();
        User::find('4')->set('bio', 'I wish I was as cool as Daniel Radcliffe!')->save();
        User::find('5')->set('bio', 'I hate radishes.')->save();

        $blueprint = Blueprint::makeFromFields(['bio' => ['type' => 'text']]);
        BlueprintRepository::shouldReceive('find')->with('user')->andReturn($blueprint);

        $query = <<<'GQL'
{
    users(filter: {
        bio: {
            contains: "rad",
            ends_with: "!"
        }
    }) {
        data {
            id
            bio
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['users' => ['data' => [
                [
                    'id' => '3',
                    'bio' => 'That was so rad!',
                ],
                [
                    'id' => '4',
                    'bio' => 'I wish I was as cool as Daniel Radcliffe!',
                ],
            ]]]]);
    }

    /** @test */
    public function it_filters_users_with_equalto_shorthand()
    {
        $this->createUsers();

        $query = <<<'GQL'
{
    users(filter: {
        email: "b@example.com"
    }) {
        data {
            id
            email
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['users' => ['data' => [
                [
                    'id' => '2',
                    'email' => 'b@example.com',
                ],
            ]]]]);
    }

    /** @test */
    public function it_filters_users_with_multiple_conditions_of_the_same_type()
    {
        $this->createUsers();

        User::find('3')->set('bio', 'This is rad')->save();
        User::find('4')->set('bio', 'This is awesome')->save();
        User::find('5')->set('bio', 'This is both rad and awesome')->save();

        $blueprint = Blueprint::makeFromFields(['bio' => ['type' => 'text']]);
        BlueprintRepository::shouldReceive('find')->with('user')->andReturn($blueprint);

        $query = <<<'GQL'
{
    users(filter: {
        bio: [
            { contains: "rad" },
            { contains: "awesome" },
        ]
    }) {
        data {
            id
            bio
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['users' => ['data' => [
                [
                    'id' => '5',
                    'bio' => 'This is both rad and awesome',
                ],
            ]]]]);
    }

    /**
     * @test
     *
     * @dataProvider userPasswordFilterProvider
     */
    public function it_doesnt_allow_filtering_users_by_password($filter)
    {
        User::make()->id('one')->email('one@domain.com')->passwordHash('abc')->save();
        User::make()->id('two')->email('two@domain.com')->passwordHash('def')->save();

        $query = <<<GQL
{
    users(filter: $filter) {
        data {
            id
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['users' => ['data' => [
                ['id' => 'one'],
                ['id' => 'two'], // this one would be filtered out if the password was allowed
            ]]]]);
    }

    public function userPasswordFilterProvider()
    {
        return [
            'password' => ['{ password: "abc" }'],
            'password:is' => ['{ password: {is: "abc"} }'],
            'password:regex' => ['{ password: {regex: "abc"} }'],
            'password_hash' => ['{ password_hash: "abc" }'],
            'password_hash:is' => ['{ password_hash: {is: "abc"} }'],
            'password_hash:regex' => ['{ password_hash: {regex: "abc"} }'],
        ];
    }

    /** @test */
    public function it_sorts_users()
    {
        $this->createUsers();

        $query = <<<'GQL'
{
    users(sort: "name") {
        data {
            id
            name
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['users' => ['data' => [
                ['id' => '6', 'name' => 'Alan'],
                ['id' => '3', 'name' => 'Burt'],
                ['id' => '1', 'name' => 'Carmen'],
                ['id' => '5', 'name' => 'Dolores'],
                ['id' => '2', 'name' => 'Edgar'],
                ['id' => '7', 'name' => 'Fred'],
                ['id' => '4', 'name' => 'Gary'],
            ]]]]);
    }

    /** @test */
    public function it_sorts_users_descending()
    {
        $this->createUsers();

        $query = <<<'GQL'
{
    users(sort: "name desc") {
        data {
            id
            name
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['users' => ['data' => [
                ['id' => '4', 'name' => 'Gary'],
                ['id' => '7', 'name' => 'Fred'],
                ['id' => '2', 'name' => 'Edgar'],
                ['id' => '5', 'name' => 'Dolores'],
                ['id' => '1', 'name' => 'Carmen'],
                ['id' => '3', 'name' => 'Burt'],
                ['id' => '6', 'name' => 'Alan'],
            ]]]]);
    }

    /** @test */
    public function it_sorts_users_on_multiple_fields()
    {
        User::make()->id('1')->email('a@example.com')->data(['foo' => 'Beta', 'number' => 2])->save();
        User::make()->id('2')->email('b@example.com')->data(['foo' => 'Alpha', 'number' => 2])->save();
        User::make()->id('3')->email('c@example.com')->data(['foo' => 'Alpha', 'number' => 1])->save();
        User::make()->id('4')->email('d@example.com')->data(['foo' => 'Beta', 'number' => 1])->save();

        $blueprint = Blueprint::makeFromFields([
            'foo' => ['type' => 'text'],
            'number' => ['type' => 'integer'],
        ]);
        BlueprintRepository::shouldReceive('find')->with('user')->andReturn($blueprint);

        $query = <<<'GQL'
{
    users(sort: ["foo", "number desc"]) {
        data {
            id
            foo
            number
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => ['users' => ['data' => [
                ['id' => '2', 'foo' => 'Alpha', 'number' => 2],
                ['id' => '3', 'foo' => 'Alpha', 'number' => 1],
                ['id' => '1', 'foo' => 'Beta', 'number' => 2],
                ['id' => '4', 'foo' => 'Beta', 'number' => 1],
            ]]]]);
    }
}
