<?php

namespace Tests\Auth;

use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\TestCase;

#[Group('user-repo')]
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

    #[Test]
    public function it_gets_the_custom_class()
    {
        Config::set('statamic.users.class', FakeEloquentUser::class);
        $this->assertInstanceOf(FakeEloquentUser::class, User::make());
    }

    #[Test]
    public function it_normalizes_to_statamic_user_from_model()
    {
        $user = User::make()->email('foo@bar.com')->data(['name' => 'foo', 'password' => 'foo']);
        $user->save();

        $normalized = User::fromUser($user->model());

        $this->assertInstanceOf(ActualEloquentUser::class, $normalized);
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
