<?php

namespace Tests\Auth\Eloquent;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\Eloquent\User as EloquentUser;
use Statamic\Auth\Eloquent\UserGroup as EloquentGroup;
use Statamic\Auth\Eloquent\UserGroupModel;
use Statamic\Facades\UserGroup;
use Statamic\Support\Str;
use Tests\TestCase;

class EloquentUserGroupTest extends TestCase
{
    use WithFaker;

    public static $migrationsGenerated = false;

    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2019, 11, 21, 23, 39, 29));

        config([
            'statamic.users.repository' => 'eloquent',
            'statamic.users.tables.groups' => 'groups',
        ]);

        $this->loadMigrationsFrom(static::migrationsDir());

        $tmpDir = static::migrationsDir().'/tmp';

        if (! self::$migrationsGenerated) {
            $this->artisan('statamic:auth:migration', ['--path' => $tmpDir]);

            self::$migrationsGenerated = true;
        }

        $this->loadMigrationsFrom($tmpDir);
    }

    private static function migrationsDir()
    {
        return __DIR__.'/__migrations__';
    }

    public function tearDown(): void
    {
        \Statamic\Facades\User::all()->each->delete();

        parent::tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        // Clean up the orphaned migration file.
        (new Filesystem)->deleteDirectory(static::migrationsDir().'/tmp');

        parent::tearDownAfterClass();
    }

    public function makeGroup()
    {
        return EloquentGroup::fromModel(UserGroupModel::create([
            'handle' => $this->faker->word,
            'title' => $this->faker->words(2, true),
            'roles' => [],
        ]));
    }

    public function makeUser()
    {
        return (new EloquentUser)
            ->model(User::create([
                'name' => $this->faker->name,
                'email' => $this->faker->unique()->safeEmail,
                // 'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
                'remember_token' => Str::random(10),
            ])
            );
    }

    #[Test]
    public function it_creates_a_group()
    {
        $group = $this->makeGroup();

        $this->assertInstanceOf(EloquentGroup::class, $group);
    }

    #[Test]
    public function it_assigns_a_group_to_a_user()
    {
        $group = $this->makeGroup();
        $user = $this->makeUser();
        $user->addToGroup($group);

        $this->assertEquals($user->groups()->first(), $group);
    }

    #[Test]
    public function it_assigns_a_group_to_a_user_then_removes_it()
    {
        $group = $this->makeGroup();
        $user = $this->makeUser();
        $user->addToGroup($group);

        $this->assertEquals($user->groups()->first(), $group);
        $this->assertCount(1, $user->groups());

        $user->removeFromGroup($group);

        $this->assertCount(0, $user->groups());
    }

    #[Test]
    public function it_sets_and_gets_data()
    {
        $group = $this->makeGroup();

        $this->assertInstanceOf(Collection::class, $data = $group->data());
        $this->assertEquals([], $data->all());

        $group->data(['alfa' => 'bravo']);
        $group->set('charlie', 'delta');
        $this->assertEquals(['alfa' => 'bravo', 'charlie' => 'delta'], $group->data()->all());

        $group->save();

        $group = UserGroup::all()->first();
        $this->assertEquals(['alfa' => 'bravo', 'charlie' => 'delta'], $group->data()->all());
    }
}
