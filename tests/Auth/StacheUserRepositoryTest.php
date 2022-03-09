<?php

namespace Tests\Auth;

use Statamic\Auth\File\User;
use Statamic\Testing\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group user-repo */
class StacheUserRepositoryTest extends TestCase
{
    use UserRepositoryTests;
    use PreventSavingStacheItemsToDisk;

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
