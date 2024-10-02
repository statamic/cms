<?php

namespace Tests\Auth\Eloquent;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User as Users;
use Statamic\Query\Scopes\Scope;
use Tests\TestCase;

class EloquentUserQueryBuilderTest extends TestCase
{
    public static $migrationsGenerated = false;

    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2019, 11, 21, 23, 39, 29));

        config([
            'statamic.users.repository' => 'eloquent',
            'auth.providers.users' => [
                'driver' => 'eloquent',
                'model' => User::class,
            ],
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
        Users::all()->each->delete();

        parent::tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        // Clean up the orphaned migration file.
        (new Filesystem)->deleteDirectory(static::migrationsDir().'/tmp');

        parent::tearDownAfterClass();
    }

    #[Test]
    public function it_queries_by_scope()
    {
        BobScope::register();
        Users::allowQueryScope(BobScope::class);
        Users::allowQueryScope(BobScope::class, 'namedBob');

        User::create(['name' => 'Jack', 'email' => 'jack@statamic.com']);
        User::create(['name' => 'Jason', 'email' => 'jason@statamic.com']);
        User::create(['name' => 'Bob Down', 'email' => 'bob@down.com']);
        User::create(['name' => 'Bob Vance', 'email' => 'bob@vancerefridgeration.com']);

        // Scope defined in model ...
        $this->assertEquals([
            'Jack', 'Jason',
        ], Users::query()->statamic()->get()->map->name->all());

        // Statamic style scope ...
        $this->assertEquals([
            'Bob Down', 'Bob Vance',
        ], Users::query()->bob()->get()->map->name->all());

        // Statamic style scope with method ...
        $this->assertEquals([
            'Bob Down', 'Bob Vance',
        ], Users::query()->namedBob()->get()->map->name->all());

        // Otherwise calling a non-existent method should throw appropriate exception ...
        try {
            Users::query()->something()->get();
            $this->fail('Undefined method exception was not thrown.');
        } catch (\BadMethodCallException $e) {
            $this->assertEquals('Call to undefined method Illuminate\\Database\\Eloquent\\Builder::something()', $e->getMessage());
        }
    }
}

class BobScope extends Scope
{
    protected static $handle = 'bob';

    public function apply($query, $context)
    {
        $query->where('name', 'like', '%bob%');
    }
}
