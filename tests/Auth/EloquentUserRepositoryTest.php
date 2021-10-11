<?php

namespace Tests\Auth;

use Tests\TestCase;

/** @group user-repo */
class EloquentUserRepositoryTest extends TestCase
{
    use UserRepositoryTests;

    public function setUp(): void
    {
        parent::setup();

        $testbench = (new \Statamic\Console\Processes\Composer(__DIR__.'/../../'))->installedVersion('orchestra/testbench-core');

        if (version_compare($testbench, '6.7.0', '<')) {
            $this->markTestSkipped('Need defineDatabaseMigrations method only introduced in 6.7.0');
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
