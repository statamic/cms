<?php

namespace Tests\Auth;

use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\File\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('user-repo')]
class StacheUserRepositoryTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use UserRepositoryTests;

    public function userClass()
    {
        return User::class;
    }

    public function fakeUserClass()
    {
        return FakeStacheUser::class;
    }

    #[Test]
    public function it_gets_the_custom_class()
    {
        Config::set('statamic.users.class', CustomFileUser::class);
        $this->assertInstanceOf(CustomFileUser::class, User::make());
    }
}

class CustomFileUser extends \Statamic\Auth\File\User {}

class FakeStacheUser extends \Statamic\Auth\File\User
{
    public function initials()
    {
        return 'FAKEINITIALS';
    }
}
