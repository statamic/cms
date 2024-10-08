<?php

namespace Tests\Data\Users;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Facades\UserGroup;
use Statamic\Query\Scopes\Scope;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UserQueryBuilderTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function users_are_found_using_or_where()
    {
        User::make()->email('gandalf@precious.com')->data(['name' => 'Gandalf'])->save();
        User::make()->email('smeagol@precious.com')->data(['name' => 'Smeagol'])->save();
        User::make()->email('frodo@precious.com')->data(['name' => 'Frodo'])->save();

        $users = User::query()->where('email', 'gandalf@precious.com')->orWhere('name', 'Frodo')->get();

        $this->assertCount(2, $users);
        $this->assertEquals(['Gandalf', 'Frodo'], $users->map->name->all());
    }

    #[Test]
    public function users_are_found_using_or_where_in()
    {
        User::make()->email('gandalf@precious.com')->data(['name' => 'Gandalf'])->save();
        User::make()->email('smeagol@precious.com')->data(['name' => 'Smeagol'])->save();
        User::make()->email('frodo@precious.com')->data(['name' => 'Frodo'])->save();
        User::make()->email('aragorn@precious.com')->data(['name' => 'Aragorn'])->save();
        User::make()->email('bombadil@precious.com')->data(['name' => 'Tommy'])->save();

        $users = User::query()->whereIn('name', ['Gandalf', 'Frodo'])->orWhereIn('name', ['Gandalf', 'Aragorn', 'Tommy'])->get();

        $this->assertCount(4, $users);
        $this->assertEquals(['Gandalf', 'Frodo', 'Aragorn', 'Tommy'], $users->map->name->all());
    }

    #[Test]
    public function users_are_found_using_or_where_not_in()
    {
        User::make()->email('gandalf@precious.com')->data(['name' => 'Gandalf'])->save();
        User::make()->email('smeagol@precious.com')->data(['name' => 'Smeagol'])->save();
        User::make()->email('frodo@precious.com')->data(['name' => 'Frodo'])->save();
        User::make()->email('aragorn@precious.com')->data(['name' => 'Aragorn'])->save();
        User::make()->email('bombadil@precious.com')->data(['name' => 'Tommy'])->save();
        User::make()->email('sauron@precious.com')->data(['name' => 'Sauron'])->save();

        $users = User::query()->whereNotIn('name', ['Gandalf', 'Frodo'])->orWhereNotIn('name', ['Gandalf', 'Sauron'])->get();

        $this->assertCount(3, $users);
        $this->assertEquals(['Smeagol', 'Aragorn', 'Tommy'], $users->map->name->all());
    }

    #[Test]
    public function users_are_found_using_nested_where()
    {
        User::make()->email('gandalf@precious.com')->data(['name' => 'Gandalf'])->save();
        User::make()->email('smeagol@precious.com')->data(['name' => 'Smeagol'])->save();
        User::make()->email('frodo@precious.com')->data(['name' => 'Frodo'])->save();
        User::make()->email('aragorn@precious.com')->data(['name' => 'Aragorn'])->save();
        User::make()->email('bombadil@precious.com')->data(['name' => 'Tommy'])->save();
        User::make()->email('sauron@precious.com')->data(['name' => 'Sauron'])->save();

        $users = User::query()
            ->where(function ($query) {
                $query->where('name', 'Gandalf');
            })
            ->orWhere(function ($query) {
                $query->where('name', 'Frodo')->orWhere('name', 'Aragorn');
            })
            ->orWhere('email', 'sauron@precious.com')
            ->get();

        $this->assertCount(4, $users);
        $this->assertEquals(['Gandalf', 'Frodo', 'Aragorn', 'Sauron'], $users->map->name->all());
    }

    #[Test]
    public function users_are_found_using_nested_where_in()
    {
        User::make()->email('gandalf@precious.com')->data(['name' => 'Gandalf'])->save();
        User::make()->email('smeagol@precious.com')->data(['name' => 'Smeagol'])->save();
        User::make()->email('frodo@precious.com')->data(['name' => 'Frodo'])->save();
        User::make()->email('aragorn@precious.com')->data(['name' => 'Aragorn'])->save();
        User::make()->email('bombadil@precious.com')->data(['name' => 'Tommy'])->save();
        User::make()->email('sauron@precious.com')->data(['name' => 'Sauron'])->save();

        $users = User::query()
            ->where(function ($query) {
                $query->where('name', 'Gandalf');
            })
            ->orWhere(function ($query) {
                $query->where('name', 'Frodo')->orWhereIn('name', ['Gandalf', 'Aragorn']);
            })
            ->orWhere('email', 'sauron@precious.com')
            ->get();

        $this->assertCount(4, $users);
        $this->assertEquals(['Gandalf', 'Frodo', 'Aragorn', 'Sauron'], $users->map->name->all());
    }

    #[Test]
    public function users_are_found_using_where_with_json_value()
    {
        User::make()->email('gandalf@precious.com')->data(['name' => 'Gandalf', 'content' => ['value' => 1]])->save();
        User::make()->email('smeagol@precious.com')->data(['name' => 'Smeagol', 'content' => ['value' => 2]])->save();
        User::make()->email('frodo@precious.com')->data(['name' => 'Frodo', 'content' => ['value' => 3]])->save();
        User::make()->email('aragorn@precious.com')->data(['name' => 'Aragorn', 'content' => ['value' => 1]])->save();
        User::make()->email('bombadil@precious.com')->data(['name' => 'Tommy', 'content' => ['value' => 2]])->save();
        User::make()->email('sauron@precious.com')->data(['name' => 'Sauron', 'content' => ['value' => 3]])->save();
        // the following two users use scalars for the content field to test that they get successfully ignored.
        User::make()->email('arwen@precious.com')->data(['name' => 'Arwen', 'content' => 'string'])->save();
        User::make()->email('bilbo@precious.com')->data(['name' => 'Bilbo', 'content' => 'string'])->save();

        $users = User::query()
            ->where('content->value', 1)
            ->get();

        $this->assertCount(2, $users);
        $this->assertEquals(['Gandalf', 'Aragorn'], $users->map->name->all());

        $users = User::query()
            ->where('content->value', '<>', 1)
            ->get();

        $this->assertCount(6, $users);
        $this->assertEquals(['Smeagol', 'Frodo', 'Tommy', 'Sauron', 'Arwen', 'Bilbo'], $users->map->name->all());
    }

    #[Test]
    public function users_are_found_using_where_column()
    {
        User::make()->email('1@test.com')->data(['foo' => 'Post 1', 'other_foo' => 'Not Post 1'])->save();
        User::make()->email('2@test.com')->data(['foo' => 'Post 2', 'other_foo' => 'Not Post 2'])->save();
        User::make()->email('3@test.com')->data(['foo' => 'Post 3', 'other_foo' => 'Post 3'])->save();
        User::make()->email('4@test.com')->data(['foo' => 'Post 4', 'other_foo' => 'Post 4'])->save();
        User::make()->email('5@test.com')->data(['foo' => 'Post 5', 'other_foo' => 'Not Post 5'])->save();

        $entries = User::query()->whereColumn('foo', 'other_foo')->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 3', 'Post 4'], $entries->map->foo->all());

        $entries = User::query()->whereColumn('foo', '!=', 'other_foo')->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['Post 1', 'Post 2', 'Post 5'], $entries->map->foo->all());
    }

    #[Test]
    public function users_are_found_using_when()
    {
        User::make()->email('gandalf@precious.com')->data(['name' => 'Gandalf'])->save();
        User::make()->email('smeagol@precious.com')->data(['name' => 'Smeagol'])->save();
        User::make()->email('frodo@precious.com')->data(['name' => 'Frodo'])->save();

        $users = User::query()->when(true, function ($query) {
            $query->where('email', 'gandalf@precious.com');
        })->get();

        $this->assertCount(1, $users);
        $this->assertEquals(['Gandalf'], $users->map->name->all());

        $users = User::query()->when(false, function ($query) {
            $query->where('email', 'gandalf@precious.com');
        })->get();

        $this->assertCount(3, $users);
        $this->assertEquals(['Gandalf', 'Smeagol', 'Frodo'], $users->map->name->all());
    }

    #[Test]
    public function users_are_found_using_unless()
    {
        User::make()->email('gandalf@precious.com')->data(['name' => 'Gandalf'])->save();
        User::make()->email('smeagol@precious.com')->data(['name' => 'Smeagol'])->save();
        User::make()->email('frodo@precious.com')->data(['name' => 'Frodo'])->save();

        $users = User::query()->unless(true, function ($query) {
            $query->where('email', 'gandalf@precious.com');
        })->get();

        $this->assertCount(3, $users);
        $this->assertEquals(['Gandalf', 'Smeagol', 'Frodo'], $users->map->name->all());

        $users = User::query()->unless(false, function ($query) {
            $query->where('email', 'gandalf@precious.com');
        })->get();

        $this->assertCount(1, $users);
        $this->assertEquals(['Gandalf'], $users->map->name->all());
    }

    #[Test]
    public function users_are_found_using_tap()
    {
        User::make()->email('gandalf@precious.com')->data(['name' => 'Gandalf'])->save();
        User::make()->email('smeagol@precious.com')->data(['name' => 'Smeagol'])->save();
        User::make()->email('frodo@precious.com')->data(['name' => 'Frodo'])->save();

        $users = User::query()->tap(function ($query) {
            $query->where('email', 'gandalf@precious.com');
        })->get();

        $this->assertCount(1, $users);
        $this->assertEquals(['Gandalf'], $users->map->name->all());
    }

    #[Test]
    public function users_are_found_using_where_group()
    {
        $groupOne = tap(UserGroup::make()->handle('one'))->save();
        $groupTwo = tap(UserGroup::make()->handle('two'))->save();

        $userOne = tap(User::make()->email('gandalf@precious.com')->data(['name' => 'Gandalf']))->save();
        $userTwo = tap(User::make()->email('smeagol@precious.com')->data(['name' => 'Smeagol']))->save();
        $userThree = tap(User::make()->email('frodo@precious.com')->data(['name' => 'Frodo']))->save();

        $userOne->addToGroup($groupOne)->save();
        $userTwo->addToGroup($groupOne)->save();
        $userThree->addToGroup($groupTwo)->save();

        $users = User::query()->whereGroup('one')->get();

        $this->assertCount(2, $users);
        $this->assertEquals(['Gandalf', 'Smeagol'], $users->map->name->all());

        $users = User::query()->whereGroup('two')->get();

        $this->assertCount(1, $users);
        $this->assertEquals(['Frodo'], $users->map->name->all());
    }

    #[Test]
    public function users_are_found_using_where_group_in()
    {
        $groupOne = tap(UserGroup::make()->handle('one'))->save();
        $groupTwo = tap(UserGroup::make()->handle('two'))->save();
        $groupThree = tap(UserGroup::make()->handle('three'))->save();

        $userOne = tap(User::make()->email('gandalf@precious.com')->data(['name' => 'Gandalf']))->save();
        $userTwo = tap(User::make()->email('smeagol@precious.com')->data(['name' => 'Smeagol']))->save();
        $userThree = tap(User::make()->email('frodo@precious.com')->data(['name' => 'Frodo']))->save();

        $userOne->addToGroup($groupOne)->save();
        $userTwo->addToGroup($groupThree)->save();
        $userThree->addToGroup($groupTwo)->save();

        $users = User::query()->whereGroupIn(['one', 'three'])->get();

        $this->assertCount(2, $users);
        $this->assertEquals(['Gandalf', 'Smeagol'], $users->map->name->all());

        $users = User::query()->whereGroupIn(['two'])->get();

        $this->assertCount(1, $users);
        $this->assertEquals(['Frodo'], $users->map->name->all());

        $users = User::query()->whereGroupIn(['one', 'two'])->orWhereGroupIn(['three'])->get();

        $this->assertCount(3, $users);
        $this->assertEquals(['Gandalf', 'Frodo', 'Smeagol'], $users->map->name->all());
    }

    #[Test]
    public function users_are_found_using_or_where_group()
    {
        $groupOne = tap(UserGroup::make()->handle('one'))->save();
        $groupTwo = tap(UserGroup::make()->handle('two'))->save();
        $groupThree = tap(UserGroup::make()->handle('three'))->save();

        $userOne = tap(User::make()->email('gandalf@precious.com')->data(['name' => 'Gandalf']))->save();
        $userTwo = tap(User::make()->email('smeagol@precious.com')->data(['name' => 'Smeagol']))->save();
        $userThree = tap(User::make()->email('frodo@precious.com')->data(['name' => 'Frodo']))->save();

        $userOne->addToGroup($groupOne)->save();
        $userTwo->addToGroup($groupThree)->save();
        $userThree->addToGroup($groupTwo)->save();

        $users = User::query()->whereGroup('one')->orWhereGroup('three')->get();

        $this->assertCount(2, $users);
        $this->assertEquals(['Gandalf', 'Smeagol'], $users->map->name->all());
    }

    #[Test]
    public function users_are_found_using_where_role()
    {
        $roleOne = tap(Role::make()->handle('one'))->save();
        $roleTwo = tap(Role::make()->handle('two'))->save();

        $userOne = tap(User::make()->email('gandalf@precious.com')->data(['name' => 'Gandalf']))->save();
        $userTwo = tap(User::make()->email('smeagol@precious.com')->data(['name' => 'Smeagol']))->save();
        $userThree = tap(User::make()->email('frodo@precious.com')->data(['name' => 'Frodo']))->save();

        $userOne->assignRole($roleOne)->save();
        $userTwo->assignRole($roleOne)->save();
        $userThree->assignRole($roleTwo)->save();

        $users = User::query()->whereRole('one')->get();

        $this->assertCount(2, $users);
        $this->assertEquals(['Gandalf', 'Smeagol'], $users->map->name->all());

        $users = User::query()->whereRole('two')->get();

        $this->assertCount(1, $users);
        $this->assertEquals(['Frodo'], $users->map->name->all());
    }

    #[Test]
    public function users_are_found_using_where_role_in()
    {
        $roleOne = tap(Role::make()->handle('one'))->save();
        $roleTwo = tap(Role::make()->handle('two'))->save();
        $roleThree = tap(Role::make()->handle('three'))->save();

        $userOne = tap(User::make()->email('gandalf@precious.com')->data(['name' => 'Gandalf']))->save();
        $userTwo = tap(User::make()->email('smeagol@precious.com')->data(['name' => 'Smeagol']))->save();
        $userThree = tap(User::make()->email('frodo@precious.com')->data(['name' => 'Frodo']))->save();

        $userOne->assignRole($roleOne)->save();
        $userTwo->assignRole($roleThree)->save();
        $userThree->assignRole($roleTwo)->save();

        $users = User::query()->whereRoleIn(['one', 'three'])->get();

        $this->assertCount(2, $users);
        $this->assertEquals(['Gandalf', 'Smeagol'], $users->map->name->all());

        $users = User::query()->whereRoleIn(['two'])->get();

        $this->assertCount(1, $users);
        $this->assertEquals(['Frodo'], $users->map->name->all());

        $users = User::query()->whereRoleIn(['one', 'two'])->orWhereRoleIn(['three'])->get();

        $this->assertCount(3, $users);
        $this->assertEquals(['Gandalf', 'Frodo', 'Smeagol'], $users->map->name->all());
    }

    #[Test]
    public function users_are_found_using_or_where_role()
    {
        $roleOne = tap(Role::make()->handle('one'))->save();
        $roleTwo = tap(Role::make()->handle('two'))->save();
        $roleThree = tap(Role::make()->handle('three'))->save();

        $userOne = tap(User::make()->email('gandalf@precious.com')->data(['name' => 'Gandalf']))->save();
        $userTwo = tap(User::make()->email('smeagol@precious.com')->data(['name' => 'Smeagol']))->save();
        $userThree = tap(User::make()->email('frodo@precious.com')->data(['name' => 'Frodo']))->save();

        $userOne->assignRole($roleOne)->save();
        $userTwo->assignRole($roleThree)->save();
        $userThree->assignRole($roleTwo)->save();

        $users = User::query()->whereRole('one')->orWhereRole('three')->get();

        $this->assertCount(2, $users);
        $this->assertEquals(['Gandalf', 'Smeagol'], $users->map->name->all());
    }

    #[Test]
    public function values_can_be_plucked()
    {
        User::make()->email('gandalf@precious.com')->data(['name' => 'Gandalf', 'type' => 'a'])->save();
        User::make()->email('smeagol@precious.com')->data(['name' => 'Smeagol', 'type' => 'b'])->save();
        User::make()->email('frodo@precious.com')->data(['name' => 'Frodo', 'type' => 'b'])->save();

        $this->assertEquals([
            'gandalf@precious.com' => 'Gandalf',
            'smeagol@precious.com' => 'Smeagol',
            'frodo@precious.com' => 'Frodo',
        ], User::query()->pluck('name', 'email')->all());

        $this->assertEquals([
            'Gandalf',
            'Smeagol',
            'Frodo',
        ], User::query()->pluck('name')->all());

        // Assert only queried values are plucked.
        $this->assertSame([
            'Smeagol',
            'Frodo',
        ], User::query()->where('type', 'b')->pluck('name')->all());
    }

    /** @test **/
    public function users_are_found_using_scopes()
    {
        CustomScope::register();
        User::allowQueryScope(CustomScope::class);
        User::allowQueryScope(CustomScope::class, 'whereCustom');

        User::make()->email('gandalf@precious.com')->data(['name' => 'Gandalf'])->save();
        User::make()->email('smeagol@precious.com')->data(['name' => 'Smeagol'])->save();

        $this->assertCount(1, User::query()->customScope(['email' => 'gandalf@precious.com'])->get());
        $this->assertCount(1, User::query()->whereCustom(['email' => 'gandalf@precious.com'])->get());
    }
}

class CustomScope extends Scope
{
    public function apply($query, $params)
    {
        $query->where('email', $params['email']);
    }
}
