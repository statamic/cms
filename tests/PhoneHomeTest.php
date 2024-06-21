<?php

namespace Tests;

use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Licensing\Outpost;

class PhoneHomeTest extends TestCase
{
    #[Test]
    #[DataProvider('algorithmProvider')]
    public function it_contacts_the_outpost($algo)
    {
        $this->assertTrue(app('router')->getRoutes()->hasNamedRoute('statamic.phone-home'));

        config(['statamic.system.license_key' => 'test-key']);

        // Assume that the key is hashed and base64 encoded. The base 64 encoding is necessary
        // because the hash might include a slash which would screw with the route parameter.
        $key = base64_encode(password_hash('test-key', $algo));

        $this->mock(Outpost::class)->shouldReceive('radio')->once();

        $this->get($url = '/et/phone/home/'.$key)->assertOk();

        // Assert that the route is rate limited to once per minute.
        $this->get($url)->assertStatus(429);
    }

    public static function algorithmProvider()
    {
        return [
            'default' => [PASSWORD_DEFAULT],
            'bcrypt' => [PASSWORD_BCRYPT],
            'argon2i' => [PASSWORD_ARGON2I],
            'argon2id' => [PASSWORD_ARGON2ID],
        ];
    }

    #[Test]
    #[DefineEnvironment('disablePhoneHome')]
    public function it_does_not_contact_the_outpost_if_disabled()
    {
        config(['statamic.system.license_key' => 'test-key']);
        $key = base64_encode(password_hash('test-key', PASSWORD_BCRYPT));
        $this->mock(Outpost::class)->shouldReceive('radio')->never();

        $this->assertFalse(app('router')->getRoutes()->hasNamedRoute('statamic.phone-home'));

        $this->get('/et/phone/home/'.$key)->assertNotFound();
    }

    public function disablePhoneHome($app)
    {
        $app['config']->set('statamic.system.phone_home_route_enabled', false);
    }

    #[Test]
    public function it_does_not_contact_the_outpost_when_an_incorrect_key_is_provided()
    {
        config(['statamic.system.license_key' => 'test-key']);

        $this->mock(Outpost::class)->shouldReceive('radio')->never();

        $this->get('/et/phone/home/invalid')->assertNotFound();
    }

    #[Test]
    public function it_does_not_contact_the_outpost_when_key_is_missing()
    {
        config(['statamic.system.license_key' => 'test-key']);

        $this->mock(Outpost::class)->shouldReceive('radio')->never();

        $this->get('/et/phone/home')->assertNotFound();
    }
}
