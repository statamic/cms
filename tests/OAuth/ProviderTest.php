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
    public function it_merges_data()
    {
        $provider = $this->provider();

        $user = $this->user()->save();

        $provider->mergeUser(new Socialite(), $user);

        $this->assertEquals(['name' => 'Foo Bar', 'extra' => 'bar'], $user->data()->all());
    }

    /** @test */
    public function it_finds_an_existing_user_by_email()
    {
        $provider = $this->provider();

        $savedUser = $this->user()->save();

        $this->assertCount(1, UserFacade::all());
        $this->assertEquals([$savedUser], UserFacade::all()->all());

        $foundUser = $provider->findOrCreateUser(new Socialite());

        $this->assertCount(1, UserFacade::all());
        $this->assertEquals([$savedUser], UserFacade::all()->all());
        $this->assertEquals($savedUser, $foundUser);
    }

    /** @test */
    public function it_finds_the_user_by_id_after_merging_the_data()
    {
        $provider = $this->provider();

        $user = UserFacade::make()->id('foo')->email('foo@bar.com')->data(['name' => 'foo', 'extra' => 'bar'])->save();

        $this->assertNull($provider->getUserId('foo-bar'));

        $provider->mergeUser(new Socialite(), $user);

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
