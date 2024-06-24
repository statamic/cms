<?php

namespace Tests\Auth\Eloquent;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\File\Role;
use Statamic\Auth\File\UserGroup;
use Statamic\Facades\Config;
use Statamic\Facades\User;
use Tests\TestCase;

class ActualUserModel extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'custom_users';

    protected function casts(): array
    {
        return [
            'preferences' => 'json',
        ];
    }
}

class EloquentQueryUserTest extends TestCase
{
    public static $migrationsGenerated = false;

    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2019, 11, 21, 23, 39, 29));

        config([
            'statamic.users.repository' => 'eloquent',
            'statamic.users.tables.users' => 'custom_users',
            'statamic.users.tables.roles' => 'role_custom_users',
            'statamic.users.tables.roles' => 'group_custom_users',
            'auth.providers.users.driver' => 'eloquent',
            'auth.providers.users.model' => ActualUserModel::class,
        ]);

        $tmpDir = static::migrationsDir().'/tmp';

        if (! self::$migrationsGenerated) {
            $this->artisan('statamic:auth:migration', ['--path' => $tmpDir]);

            self::$migrationsGenerated = true;
        }

        $this->loadMigrationsFrom($tmpDir);

        Schema::table(config('statamic.users.tables.users', 'users'), function (Blueprint $table) {
            $table->string('password')->nullable()->change();
        });

    }

    private static function migrationsDir()
    {
        return __DIR__.'/__migrations__';
    }

    public function tearDown(): void
    {
        // Prevent error about null password during the down migration.
        User::all()->each->delete();

        parent::tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        // Clean up the orphaned migration file.
        (new Filesystem)->deleteDirectory(static::migrationsDir().'/tmp');

        parent::tearDownAfterClass();
    }

    #[Test]
    public function users_are_found_using_where_group()
    {
        $groupOne = tap(\Statamic\Facades\UserGroup::make()->handle('one'))->save();
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
        $this->assertEquals(['Gandalf', 'Smeagol', 'Frodo'], $users->map->name->all());
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
        Config::set('users.tables.users', 'lotr_users');

        $roleOne = tap(\Statamic\Facades\Role::make()->handle('one')->title('one'))->save();
        $roleTwo = tap(Role::make()->handle('two')->title('two'))->save();

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
        $roleOne = tap(Role::make()->handle('one')->title('one'))->save();
        $roleTwo = tap(Role::make()->handle('two')->title('two'))->save();
        $roleThree = tap(Role::make()->handle('three')->title('three'))->save();

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
        $this->assertEquals(['Gandalf', 'Smeagol', 'Frodo'], $users->map->name->all());
    }

    #[Test]
    public function users_are_found_using_or_where_role()
    {
        $roleOne = tap(Role::make()->handle('one')->title('one'))->save();
        $roleTwo = tap(Role::make()->handle('two')->title('two'))->save();
        $roleThree = tap(Role::make()->handle('three')->title('three'))->save();

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
}
