<?php

namespace Tests\OAuth;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Exceptions\OAuthEmailExistsException;
use Statamic\Facades\User as UserFacade;
use Statamic\OAuth\Provider;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ProviderTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $tempDir;

    public function setUp(): void
    {
        parent::setUp();

        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => $this->tempDir = __DIR__.'/tmp',
        ]]);
    }

    public function tearDown(): void
    {
        app('files')->deleteDirectory($this->tempDir);
        app('files')->deleteDirectory(storage_path('statamic/oauth'));

        parent::tearDown();
    }

    #[Test]
    public function it_gets_the_config()
    {
        $this->assertEquals([], (new Provider('test'))->config());

        $this->assertEquals(['foo' => 'bar'], (new Provider('test', ['foo' => 'bar']))->config());
    }

    #[Test]
    public function it_gets_the_label_through_the_config()
    {
        $this->assertEquals('Test', (new Provider('test'))->label());

        $this->assertEquals('Foo Bar', (new Provider('test', ['label' => 'Foo Bar']))->label());
    }

    #[Test]
    public function it_gets_user_data()
    {
        $data = $this->provider()->userData($this->socialite());

        $this->assertEquals(['name' => 'Foo Bar'], $data);
    }

    #[Test]
    public function it_gets_user_data_using_a_callback()
    {
        $provider = $this->provider();
        $provider->withUserData(fn () => ['custom' => 'data']);

        $data = $provider->userData($this->socialite());

        $this->assertEquals(['custom' => 'data'], $data);
    }

    #[Test]
    public function it_merges_data()
    {
        $provider = $this->provider();

        $user = $this->user()->save();

        $this->assertEquals(['name' => 'foo', 'extra' => 'bar'], $user->data()->all());

        $provider->mergeUser($user, $this->socialite());

        $this->assertEquals(['name' => 'Foo Bar', 'extra' => 'bar'], $user->data()->all());
    }

    #[Test]
    public function it_makes_a_user()
    {
        $this->assertCount(0, UserFacade::all());

        $user = $this->provider()->makeUser($this->socialite());

        $this->assertNotNull($user);
        $this->assertEquals('foo@bar.com', $user->email());
        $this->assertEquals('Foo Bar', $user->name());
    }

    #[Test]
    public function it_makes_a_user_using_a_callback()
    {
        $this->assertCount(0, UserFacade::all());

        $provider = $this->provider();
        $provider->withUser(fn ($socialite) => UserFacade::make()->email($socialite->getEmail())->data(['very' => 'custom']));
        $user = $provider->makeUser($this->socialite());

        $this->assertNotNull($user);
        $this->assertEquals('foo@bar.com', $user->email());
        $this->assertEquals(['very' => 'custom'], $user->data()->all());
    }

    #[Test]
    public function it_creates_a_user()
    {
        $this->assertCount(0, UserFacade::all());

        $provider = $this->provider();
        $provider->createUser($this->socialite());

        $this->assertCount(1, UserFacade::all());
        $user = UserFacade::all()->get(0);
        $this->assertNotNull($user);
        $this->assertEquals('foo@bar.com', $user->email());
        $this->assertEquals('Foo Bar', $user->name());
        $this->assertEquals($user->id(), $provider->getUserId('foo-bar'));
    }

    #[Test]
    public function it_throws_when_creating_a_user_whose_email_already_exists()
    {
        $this->user()->save();

        $this->assertCount(1, UserFacade::all());

        try {
            $this->provider()->createUser($this->socialite());
            $this->fail('Exception was not thrown.');
        } catch (OAuthEmailExistsException $e) {
            $this->assertCount(1, UserFacade::all());
            $this->assertNull($this->provider()->getUserId('foo-bar'));
        }
    }

    #[Test]
    public function it_finds_an_existing_user_via_find_user_method()
    {
        $provider = $this->provider();

        $savedUser = $this->user()->save();
        $provider->setUserProviderId($savedUser, 'foo-bar');

        $this->assertCount(1, UserFacade::all());
        $this->assertEquals([$savedUser], UserFacade::all()->all());

        $foundUser = $provider->findUser($this->socialite());

        $this->assertCount(1, UserFacade::all());
        $this->assertEquals([$savedUser], UserFacade::all()->all());
        $this->assertEquals($savedUser, $foundUser);
    }

    #[Test]
    public function it_does_not_find_a_user_by_email_via_find_user_method()
    {
        $provider = $this->provider();

        // A user exists with the same email, but is not connected to the provider.
        $this->user()->save();

        $this->assertNull($provider->findUser($this->socialite()));
    }

    #[Test]
    public function it_does_not_find_or_create_a_user_via_find_user_method()
    {
        $this->assertCount(0, UserFacade::all());

        $provider = $this->provider();
        $foundUser = $provider->findUser($this->socialite());

        $this->assertNull($foundUser);

        $this->assertCount(0, UserFacade::all());
        $user = UserFacade::all()->get(0);
        $this->assertNull($user);
    }

    #[Test]
    public function it_finds_an_existing_user_via_find_or_create_user_method()
    {
        $provider = $this->provider();

        $savedUser = $this->user()->save();
        $provider->setUserProviderId($savedUser, 'foo-bar');

        $this->assertCount(1, UserFacade::all());
        $this->assertEquals([$savedUser], UserFacade::all()->all());
        $this->assertEquals('foo', $savedUser->name);

        $foundUser = $provider->findOrCreateUser($this->socialite());

        $this->assertCount(1, UserFacade::all());
        $this->assertEquals([$savedUser], UserFacade::all()->all());
        $this->assertEquals($savedUser, $foundUser);
        $this->assertEquals('Foo Bar', $savedUser->name);
    }

    #[Test]
    public function it_finds_an_existing_user_via_find_or_create_user_method_but_doesnt_merge_data()
    {
        config(['statamic.oauth.merge_user_data' => false]);

        $provider = $this->provider();

        $savedUser = $this->user()->save();
        $provider->setUserProviderId($savedUser, 'foo-bar');

        $this->assertCount(1, UserFacade::all());
        $this->assertEquals([$savedUser], UserFacade::all()->all());
        $this->assertEquals('foo', $savedUser->name);

        $foundUser = $provider->findOrCreateUser($this->socialite());

        $this->assertCount(1, UserFacade::all());
        $this->assertEquals([$savedUser], UserFacade::all()->all());
        $this->assertEquals($savedUser, $foundUser);
        $this->assertEquals('foo', $savedUser->name);
    }

    #[Test]
    public function it_creates_a_user_via_find_or_create_user_method()
    {
        $this->assertCount(0, UserFacade::all());

        $provider = $this->provider();
        $provider->findOrCreateUser($this->socialite());

        $this->assertCount(1, UserFacade::all());
        $user = UserFacade::all()->get(0);
        $this->assertNotNull($user);
        $this->assertEquals('foo@bar.com', $user->email());
        $this->assertEquals('Foo Bar', $user->name());
        $this->assertEquals($user->id(), $provider->getUserId('foo-bar'));
    }

    #[Test]
    public function it_determines_whether_a_user_is_connected()
    {
        $provider = $this->provider();

        $one = UserFacade::make()->id('one')->email('one@bar.com')->save();
        $two = UserFacade::make()->id('two')->email('two@bar.com')->save();
        $provider->setUserProviderId($one, 'one-sub');

        $this->assertTrue($provider->isConnectedTo($one));
        $this->assertFalse($provider->isConnectedTo($two));
    }

    #[Test]
    public function it_forgets_a_user()
    {
        $provider = $this->provider();

        $one = UserFacade::make()->id('one')->email('one@bar.com')->save();
        $two = UserFacade::make()->id('two')->email('two@bar.com')->save();
        $provider->setUserProviderId($one, 'one-sub');
        $provider->setUserProviderId($two, 'two-sub');

        $provider->forgetUser($one);

        $this->assertNull($provider->getUserId('one-sub'));
        $this->assertEquals('two', $provider->getUserId('two-sub'));
    }

    private function provider()
    {
        return new Provider('test');
    }

    private function user()
    {
        return UserFacade::make()->id('foo')->email('foo@bar.com')->data(['name' => 'foo', 'extra' => 'bar']);
    }

    private function socialite()
    {
        return new Socialite();
    }
}

class Socialite
{
    public function getId()
    {
        return 'foo-bar';
    }

    public function getName()
    {
        return 'Foo Bar';
    }

    public function getEmail()
    {
        return 'foo@bar.com';
    }
}
