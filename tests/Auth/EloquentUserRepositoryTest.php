<?php

namespace Tests\Auth;

use Tests\TestCase;

/** @group user-repo */
class EloquentUserRepositoryTest extends TestCase
{
    use UserRepositoryTests;

    public function setUp(): void
    {
        parent::setUp();

        if (version_compare($this->app->version(), '7.0', '<')) {
            // honestly just a pain to support this in earlier versions of laravel/testbench
            $this->markTestSkipped('Needs newer testbench');
        }
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('statamic.users.repository', 'eloquent');
        $app['config']->set('auth.providers', [
            'users' => [
                'driver' => 'eloquent',
                'model' => ActualUserModel::class,
            ],
        ]);

        // Just so we can override saveToDatabase()
        app()->bind(\Statamic\Auth\Eloquent\User::class, ActualEloquentUser::class);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
    }

    public function userClass()
    {
        return ActualEloquentUser::class;
    }

    public function fakeUserClass()
    {
        return FakeEloquentUser::class;
    }
}

class ActualEloquentUser extends \Statamic\Auth\Eloquent\User
{
    public function saveToDatabase()
    {
        $this->model()->save();

        // dont save roles/groups
    }
}

class FakeEloquentUser extends ActualEloquentUser
{
    public function initials()
    {
        return 'FAKEINITIALS';
    }
}

class ActualUserModel extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'users';
}
