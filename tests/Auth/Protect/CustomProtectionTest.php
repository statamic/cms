<?php

namespace Tests\Auth\Protect;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\Protect\ProtectorManager;
use Statamic\Auth\Protect\Protectors\Protector;

class CustomProtectionTest extends PageProtectionTestCase
{
    #[Test]
    public function the_config_and_scheme_are_set_on_custom_protectors()
    {
        config(['statamic.protect.schemes.test' => $config = [
            'driver' => 'custom',
            'config_var' => 'custom_value',
        ]]);

        $protector = $this->mock(Protector::class);
        $protector->shouldReceive('setConfig')->with($config)->once()->andReturnSelf();
        $protector->shouldReceive('setScheme')->with('test')->once()->andReturnSelf();

        app(ProtectorManager::class)->extend('custom', function () use ($protector) {
            return $protector;
        });

        $this->requestPageProtectedBy('test');
    }
}
