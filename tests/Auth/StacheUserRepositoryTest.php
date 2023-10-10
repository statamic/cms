<?php

namespace Tests\Auth;

use Statamic\Auth\File\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group user-repo */
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
