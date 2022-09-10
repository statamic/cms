<?php

namespace Tests\OAuth;

use Statamic\Facades\User as UserFacade;
use Statamic\OAuth\Provider;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ProviderTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

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

    /** @test */
    public function it_gets_user_data()
    {
        $data = $this->provider()->userData($this->socialite());

        $this->assertEquals(['name' => 'Foo Bar'], $data);
    }

    /** @test */
    public function it_gets_user_data_using_a_callback()
    {
        $provider = $this->provider();
        $provider->withUserData(fn () => ['custom' => 'data']);

        $data = $provider->userData($this->socialite());

        $this->assertEquals(['custom' => 'data'], $data);
    }

    /** @test */
    public function it_merges_data()
    {
        $provider = $this->provider();

        $user = $this->user()->save();

        $provider->mergeUser($user, $this->socialite());

        $this->assertEquals(['name' => 'Foo Bar', 'extra' => 'bar'], $user->data()->all());
    }

    /** @test */
    public function it_makes_a_user()
    {
        $this->assertCount(0, UserFacade::all());

        $user = $this->provider()->makeUser($this->socialite());

        $this->assertNotNull($user);
        $this->assertEquals('foo@bar.com', $user->email());
        $this->assertEquals('Foo Bar', $user->name());
    }

    /** @test */
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

    /** @test */
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

    /** @test */
    public function it_finds_an_existing_user_by_email()
    {
        $provider = $this->provider();

        $savedUser = $this->user()->save();

        $this->assertCount(1, UserFacade::all());
        $this->assertEquals([$savedUser], UserFacade::all()->all());

        $foundUser = $provider->findOrCreateUser($this->socialite());

        $this->assertCount(1, UserFacade::all());
        $this->assertEquals([$savedUser], UserFacade::all()->all());
        $this->assertEquals($savedUser, $foundUser);
    }

    /** @test */
    public function it_gets_the_user_by_id_after_merging_data()
    {
        $provider = $this->provider();

        $user = UserFacade::make()->id('foo')->email('foo@bar.com')->data(['name' => 'foo', 'extra' => 'bar'])->save();

        $this->assertNull($provider->getUserId('foo-bar'));

        $provider->mergeUser($user, $this->socialite());

        $this->assertEquals('foo', $provider->getUserId('foo-bar'));
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
