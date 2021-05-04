<?php

namespace Tests\Auth\Protect;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Auth\Protect\Protection;
use Statamic\Auth\Protect\ProtectorManager;
use Statamic\Auth\Protect\Protectors\Authenticated;
use Statamic\Auth\Protect\Protectors\Fallback;
use Statamic\Auth\Protect\Protectors\NullProtector;
use Statamic\Auth\Protect\Protectors\Protector;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class ProtectionTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        config(['statamic.protect.default' => null]);

        $this->protection = app(Protection::class);
    }

    /** @test */
    public function it_sets_and_gets_the_data()
    {
        $this->assertNull($this->protection->data());

        $return = $this->protection->setData($entry = $this->createEntryWithScheme('test'));

        $this->assertEquals($this->protection, $return);
        $this->assertEquals($entry, $this->protection->data());
    }

    /** @test */
    public function scheme_comes_from_data()
    {
        $this->assertNull($this->protection->scheme());

        $this->protection->setData($this->createEntryWithScheme('logged_in'));

        $this->assertEquals('logged_in', $this->protection->scheme());
    }

    /** @test */
    public function if_the_data_isnt_protectable_it_doesnt_get_a_scheme()
    {
        $this->assertNull($this->protection->scheme());

        $this->protection->setData(new class
        {
            //
        });

        $this->assertNull($this->protection->scheme());
    }

    /** @test */
    public function sitewide_scheme_comes_from_the_default_setting()
    {
        config(['statamic.protect.default' => 'logged_in']);
        config(['statamic.protect.schemes.logged_in' => [
            'driver' => 'auth',
            'form_url' => '/login',
        ]]);

        $this->assertEquals('logged_in', $this->protection->scheme());
    }

    /** @test */
    public function driver_comes_from_schemes_driver_key()
    {
        config(['statamic.protect.schemes.custom_auth_scheme' => [
            'driver' => 'auth',
        ]]);

        $this->protection->setData($this->createEntryWithScheme('custom_auth_scheme'));

        $this->assertInstanceOf(Authenticated::class, $this->protection->driver());
    }

    /** @test */
    public function no_scheme_returns_a_null_driver()
    {
        $this->assertInstanceOf(NullProtector::class, $this->protection->driver());
    }

    /** @test */
    public function invalid_driver_returns_a_fallback_driver()
    {
        $this->protection->setData($this->createEntryWithScheme('invalid'));

        $this->assertInstanceOf(Fallback::class, $this->protection->driver());
    }

    /** @test */
    public function it_protects_through_the_driver()
    {
        config(['statamic.protect.schemes.test' => [
            'driver' => 'test',
        ]]);

        $state = (object) ['protected' => false];

        app(ProtectorManager::class)->extend('test', function ($app) use ($state) {
            return new class($state) extends Protector
            {
                public function __construct($state)
                {
                    $this->state = $state;
                }

                public function protect()
                {
                    $this->state->protected = true;
                }
            };
        });

        $this->protection->setData($this->createEntryWithScheme('test'));

        $this->protection->protect();

        $this->assertTrue($state->protected);
    }

    private function createEntryWithScheme($scheme)
    {
        return EntryFactory::id('test')
            ->collection('test')
            ->data(['protect' => $scheme])
            ->make();
    }
}
