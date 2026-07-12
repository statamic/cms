<?php

namespace Tests\Auth;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Exceptions\UserNotFoundException;
use Statamic\Facades\User;

trait UserRepositoryTests
{
    abstract public function userClass();

    abstract public function fakeUserClass();

    #[Test]
    public function it_gets_the_class()
    {
        $this->assertInstanceOf($this->userClass(), User::make());
    }

    #[Test]
    public function it_overrides_the_class()
    {
        app()->bind(\Statamic\Contracts\Auth\User::class, $this->fakeUserClass());

        $this->assertInstanceOf($this->fakeUserClass(), $user = User::make());
        $this->assertEquals('FAKEINITIALS', $user->initials());
    }

    #[Test]
    public function it_gets_all_users()
    {
        User::make()->email('foo@bar.com')->data(['name' => 'foo', 'password' => 'foo'])->save();
        $this->assertEveryItemIsInstanceOf($this->userClass(), User::all());
    }

    #[Test]
    public function it_gets_all_users_with_overridden_classes()
    {
        app()->bind(\Statamic\Contracts\Auth\User::class, $this->fakeUserClass());

        User::make()->email('foo@bar.com')->data(['name' => 'foo', 'password' => 'foo'])->save();
        $this->assertEveryItemIsInstanceOf($this->fakeUserClass(), User::all());
    }

    #[Test]
    public function it_gets_user_by_id()
    {
        User::make()->id(1)->email('foo@bar.com')->data(['name' => 'foo', 'password' => 'foo'])->save();
        $this->assertInstanceOf($this->userClass(), User::find(1));
    }

    #[Test]
    public function it_gets_user_by_id_with_overridden_classes()
    {
        app()->bind(\Statamic\Contracts\Auth\User::class, $this->fakeUserClass());

        User::make()->id(1)->email('foo@bar.com')->data(['name' => 'foo', 'password' => 'foo'])->save();
        $this->assertInstanceOf($this->fakeUserClass(), User::find(1));
    }

    #[Test]
    public function it_gets_user_by_email()
    {
        User::make()->email('foo@bar.com')->data(['name' => 'foo', 'password' => 'foo'])->save();
        $this->assertInstanceOf($this->userClass(), User::findByEmail('foo@bar.com'));
    }

    #[Test]
    public function it_gets_user_by_email_with_overridden_classes()
    {
        app()->bind(\Statamic\Contracts\Auth\User::class, $this->fakeUserClass());

        User::make()->email('foo@bar.com')->data(['name' => 'foo', 'password' => 'foo'])->save();
        $this->assertInstanceOf($this->fakeUserClass(), User::findByEmail('foo@bar.com'));
    }

    #[Test]
    public function find_or_fail_gets_user()
    {
        User::make()->id(123)->email('foo@bar.com')->data(['name' => 'foo', 'password' => 'foo'])->save();
        $this->assertInstanceOf($this->userClass(), User::findOrFail(123));
    }

    #[Test]
    public function find_or_fail_throws_exception_when_user_does_not_exist()
    {
        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('User [does-not-exist] not found');

        User::findOrFail('does-not-exist');
    }

    #[Test]
    public function it_normalizes_statamic_user()
    {
        $user = User::make()->email('foo@bar.com')->data(['name' => 'foo', 'password' => 'foo']);
        $user->save();

        $this->assertInstanceOf($this->userClass(), User::fromUser($user));
    }

    #[Test]
    public function it_successfully_returns_null_when_trying_to_normalize_user_from_null()
    {
        $this->assertNull(User::fromUser(null));
    }
}
