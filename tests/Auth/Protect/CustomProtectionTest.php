<?php

namespace Tests\Auth\Protect;


use Statamic\Auth\Protect\ProtectorManager;
use Statamic\Auth\Protect\Protectors\Protector;

class CustomProtectionTest extends PageProtectionTestCase
{

    /** @test */
    public function the_config_and_scheme_are_set_on_custom_protectors()
    {
        $protector = $this->mock(Protector::class);
        $configValues = [
            'driver'     => 'custom',
            'config_var' => 'custom_value',
        ];

        config(['statamic.protect.schemes.custom' => $configValues]);

        app(ProtectorManager::class)->extend('custom', function ($app) use ($protector) {
            return $protector;
        });

        $protector->shouldReceive('setConfig')
                  ->with($configValues)
                  ->once()
                  ->andReturn($protector);

        $protector->shouldReceive('setScheme')
                  ->with('custom')
                  ->once()
                  ->andReturn($protector);

        $this->requestPageProtectedBy('custom');
    }
}