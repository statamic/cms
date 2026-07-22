<?php

namespace Tests\Auth\Eloquent;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\Eloquent\OAuthProvider;
use Statamic\Facades\OAuth;
use Statamic\Facades\User as UserFacade;
use Statamic\OAuth\Provider;
use Tests\TestCase;

class EloquentOAuthTest extends TestCase
{
    use WithFaker;

    public static $migrationsGenerated = false;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('statamic.users.repository', 'eloquent');
        $app['config']->set('statamic.oauth.enabled', true);
        $app['config']->set('statamic.oauth.providers', ['test' => 'Test']);
    }

    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2019, 11, 21, 23, 39, 29));

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
        UserFacade::all()->each->delete();

        // In case a test falls through to the file-backed default (which
        // would indicate the eloquent binding didn't take effect).
        app('files')->deleteDirectory(storage_path('statamic/oauth'));

        parent::tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        (new Filesystem)->deleteDirectory(static::migrationsDir().'/tmp');

        parent::tearDownAfterClass();
    }

    private function makeUser(?string $email = null)
    {
        return UserFacade::make()
            ->email($email ?? $this->faker->unique()->safeEmail)
            ->data(['name' => $this->faker->name])
            ->save();
    }

    #[Test]
    public function the_provider_is_eloquent_backed_when_the_eloquent_repository_is_active()
    {
        $this->assertInstanceOf(OAuthProvider::class, OAuth::provider('test'));
    }

    #[Test]
    public function the_provider_class_is_chosen_fresh_each_time_rather_than_baked_in_at_boot()
    {
        // Manager::providers() checks config('statamic.users.repository')
        // live rather than being swapped via a container binding decided at
        // boot time, specifically so that switching the repository at
        // runtime (as Tests\Auth\Eloquent\EloquentUserGroupTest and others
        // already rely on being able to do) keeps working.
        config(['statamic.users.repository' => 'file']);
        $this->assertInstanceOf(Provider::class, OAuth::provider('test'));
        $this->assertNotInstanceOf(OAuthProvider::class, OAuth::provider('test'));

        config(['statamic.users.repository' => 'eloquent']);
        $this->assertInstanceOf(OAuthProvider::class, OAuth::provider('test'));
    }

    #[Test]
    public function it_persists_a_provider_link_to_the_database_instead_of_a_file()
    {
        $user = $this->makeUser();
        $provider = OAuth::provider('test');

        $this->assertFalse($provider->isConnectedTo($user));

        $provider->setUserProviderId($user, 'sub-123');

        $this->assertTrue($provider->isConnectedTo($user));

        $this->assertDatabaseHas('oauth_connections', [
            'provider' => 'test',
            'user_id' => $user->id(),
            'provider_user_id' => 'sub-123',
        ]);

        $this->assertFalse(is_file(storage_path('statamic/oauth/test.php')));
    }

    #[Test]
    public function it_resolves_a_user_id_by_provider_id()
    {
        $user = $this->makeUser();
        $provider = OAuth::provider('test');

        $provider->setUserProviderId($user, 'sub-456');

        $this->assertEquals($user->id(), $provider->getUserId('sub-456'));
        $this->assertNull($provider->getUserId('unknown-id'));
    }

    #[Test]
    public function it_forgets_a_provider_link()
    {
        $user = $this->makeUser();
        $provider = OAuth::provider('test');

        $provider->setUserProviderId($user, 'sub-789');
        $provider->forgetUser($user);

        $this->assertFalse($provider->isConnectedTo($user));
        $this->assertEquals(0, DB::table('oauth_connections')->where('provider', 'test')->count());
    }

    #[Test]
    public function it_keeps_other_users_unaffected_when_one_link_is_updated()
    {
        $userA = $this->makeUser();
        $userB = $this->makeUser();
        $provider = OAuth::provider('test');

        $provider->setUserProviderId($userA, 'sub-a');
        $provider->setUserProviderId($userB, 'sub-b');

        $this->assertEquals($userA->id(), $provider->getUserId('sub-a'));
        $this->assertEquals($userB->id(), $provider->getUserId('sub-b'));
    }

    #[Test]
    public function a_guest_logging_in_for_the_first_time_is_created_and_linked_via_the_eloquent_backend()
    {
        // Mirrors Tests\OAuth\OAuthCallbackTest::guest_with_a_new_email_is_created_and_logged_in(),
        // against the eloquent repository instead of the default file one.
        $this->assertCount(0, UserFacade::all());

        $this->fakeProvider('test', [], 'sub-1', 'new@example.com');

        $response = $this
            ->withSession(['statamic.oauth.guard' => 'web'])
            ->get(route('statamic.oauth.callback', 'test'));

        $this->assertCount(1, UserFacade::all());
        $user = UserFacade::findByEmail('new@example.com');
        $this->assertNotNull($user);
        $this->assertAuthenticated();
        $this->assertEquals($user->id(), auth('web')->id());

        $this->assertEquals($user->id(), OAuth::provider('test')->getUserId('sub-1'));
        $this->assertDatabaseHas('oauth_connections', [
            'provider' => 'test',
            'user_id' => $user->id(),
            'provider_user_id' => 'sub-1',
        ]);
        $this->assertFalse(is_file(storage_path('statamic/oauth/test.php')));

        $response->assertRedirect();
    }

    /**
     * Replace the provider with a real one whose only mocked method is
     * getSocialiteUser(), so the Socialite facade is never called but the
     * storage map and user lookups behave for real. Mirrors
     * Tests\OAuth\OAuthCallbackTest's helper of the same name.
     */
    private function fakeProvider(string $name, array $config, string $id, string $email, string $displayName = 'Foo Bar'): void
    {
        $socialiteUser = new class($id, $email, $displayName)
        {
            public function __construct(private string $id, private string $email, private string $name)
            {
            }

            public function getId()
            {
                return $this->id;
            }

            public function getEmail()
            {
                return $this->email;
            }

            public function getName()
            {
                return $this->name;
            }
        };

        $provider = Mockery::mock(OAuthProvider::class.'[getSocialiteUser]', [$name, $config]);
        $provider->shouldReceive('getSocialiteUser')->andReturn($socialiteUser);

        OAuth::partialMock()->shouldReceive('provider')->with($name)->andReturn($provider);
    }
}
