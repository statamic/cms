<?php

namespace Tests\Stache\Repositories;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\UserCollection;
use Statamic\Contracts\Auth\User;
use Statamic\Stache\Repositories\UserRepository;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\UsersStore;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    private $repo;

    public function setUp(): void
    {
        parent::setUp();

        $stache = (new Stache)->sites(['en', 'fr']);
        $stache->registerStore((new UsersStore($stache, app('files')))->directory(__DIR__.'/../__fixtures__/users'));

        $this->repo = new UserRepository($stache);
    }

    #[Test]
    public function it_gets_all_users()
    {
        $users = $this->repo->all();

        $this->assertInstanceOf(UserCollection::class, $users);
        $this->assertCount(2, $users);
        $this->assertEveryItemIsInstanceOf(User::class, $users);

        $ordered = $users->sortBy->id()->values();
        $this->assertEquals(['users-jane', 'users-john'], $ordered->map->id()->all());
        $this->assertEquals(['jane@example.com', 'john@example.com'], $ordered->map->email()->all());
        $this->assertEquals(['Jane Doe', 'John Smith'], $ordered->map->get('name')->all());
    }

    #[Test]
    public function it_gets_a_user_by_id()
    {
        tap($this->repo->find('users-john'), function ($user) {
            $this->assertInstanceOf(User::class, $user);
            $this->assertEquals('users-john', $user->id());
            $this->assertEquals('john@example.com', $user->email());
            $this->assertEquals('John Smith', $user->get('name'));
        });

        tap($this->repo->find('users-jane'), function ($user) {
            $this->assertInstanceOf(User::class, $user);
            $this->assertEquals('users-jane', $user->id());
            $this->assertEquals('jane@example.com', $user->email());
            $this->assertEquals('Jane Doe', $user->get('name'));
        });

        $this->assertNull($this->repo->find('unknown'));
    }
}
