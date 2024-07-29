<?php

namespace Tests\Auth;

use PHPUnit\Framework\Attributes\Group;
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
}

class FakeStacheUser extends \Statamic\Auth\File\User
{
    public function initials()
    {
        return 'FAKEINITIALS';
    }
}
