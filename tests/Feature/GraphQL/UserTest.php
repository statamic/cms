<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use Statamic\Facades\GraphQL;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class UserTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use CreatesQueryableTestEntries;

    public function setUp(): void
    {
        parent::setUp();

        BlueprintRepository::partialMock();
    }

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

    /** @test */
    public function it_queries_an_user_by_id()
    {
        $this->createUsers();

        $query = <<<'GQL'
{
    user(id: "3") {
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
                    'name' => 'Burt',
                ],
            ]]);
    }

    /** @test */
    public function it_queries_an_user_by_email()
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
                    'name' => 'Burt',
                ],
            ]]);
    }

    /** @test */
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
}
