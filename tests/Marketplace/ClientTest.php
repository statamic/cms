<?php

namespace Tests\Marketplace;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Marketplace\Client;
use Tests\TestCase;

class ClientTest extends TestCase
{
    #[Test]
    public function it_uses_the_production_marketplace_by_default()
    {
        $this->assertEquals(
            'https://statamic.com/api/v1/marketplace/packages',
            (new Client)->requestEndpoint('packages')
        );
    }

    #[Test]
    public function it_adds_a_scheme_to_statamic_domain()
    {
        $this->app->instance('env', 'testing');
        putenv('STATAMIC_DOMAIN=statamic.com.test');
        $_ENV['STATAMIC_DOMAIN'] = 'statamic.com.test';

        $this->assertEquals(
            'https://statamic.com.test/api/v1/marketplace/packages',
            (new Client)->requestEndpoint('packages')
        );

        putenv('STATAMIC_DOMAIN');
        unset($_ENV['STATAMIC_DOMAIN']);
    }

    #[Test]
    public function it_falls_back_to_a_custom_licensing_url()
    {
        config(['statamic.system.licensing_url' => 'https://statamic.com.test']);

        $this->assertEquals(
            'https://statamic.com.test/api/v1/marketplace/packages',
            (new Client)->requestEndpoint('packages')
        );
    }
}
