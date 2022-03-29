<?php

namespace Tests\Auth\Eloquent;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Statamic\Auth\Eloquent\User as EloquentUser;
use Statamic\Auth\Eloquent\UserGroup as EloquentGroup;
use Statamic\Auth\Eloquent\UserGroupModel;
use Statamic\Console\Please\Kernel;
use Tests\TestCase;

class EloquentGroupTest extends TestCase
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

        $this->migrationsDir = __DIR__.'/__migrations__';

        $this->loadMigrationsFrom($this->migrationsDir);

        $tmpDir = $this->migrationsDir.'/tmp';

        if (! self::$migrationsGenerated) {
            (new Filesystem)->deleteDirectory($tmpDir);
            $this->please('auth:migration', ['--path' => $tmpDir]);

            self::$migrationsGenerated = true;
        }

        $this->loadMigrationsFrom($tmpDir);
    }

    public function tearDown(): void
    {
        // our down() migration sets password to not be nullable, so change it back
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable(true)->change();
        });
    }

    private function please($command, $parameters = [])
    {
        return $this->app[Kernel::class]->call($command, $parameters);
    }

    public function makeGroup()
    {
        return (new EloquentGroup)
            ->model(UserGroupModel::create([
                'handle' => $this->faker->word,
                'title' => $this->faker->words(2, true),
                'roles' => [],
            ])
        );
    }

    public function makeUser()
    {
        return (new EloquentUser)
            ->model(User::create([
                'name' => $this->faker->name,
                'email' => $this->faker->unique()->safeEmail,
                // 'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
                'remember_token' => str_random(10),
            ])
        );
    }

    /** @test */
    public function it_creates_a_group()
    {
        $group = $this->makeGroup();

        $this->assertInstanceOf(EloquentGroup::class, $group);
    }
}
