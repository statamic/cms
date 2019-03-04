<?php

namespace Tests\Eloquent\Auth;

use Tests\TestCase;
use Statamic\Eloquent\Auth\User;
use Statamic\Eloquent\Auth\Model;
use Tests\Auth\UserContractTests;

/** @group user */
class UserTest extends TestCase
{
    use UserContractTests;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/__migrations__');
    }

    function makeUser() {
        return (new User)->model(new Model);
    }
}
