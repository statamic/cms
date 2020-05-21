<?php

namespace Tests\Licensing;

use Statamic\Facades\Addon;
use Statamic\Licensing\AddonLicense;

use Tests\TestCase;

class AddonLicenseTest extends TestCase
{
    use LicenseTests;

    protected function license($response = [])
    {
        Addon::shouldReceive('get')->with('test/addon')
            ->andReturn(new FakeAddonLicenseAddon('Test Addon', '1.2.3'));
        $this->addToAssertionCount(-1); // dont need to assert this.

        return new AddonLicense('test/addon', $response);
    }

    /** @test */
    public function it_gets_the_addons_name()
    {
        $this->assertEquals($this->license()->name(), 'Test Addon');
    }

    /** @test */
    public function it_gets_the_addons_version()
    {
        $this->assertEquals($this->license()->version(), '1.2.3');
    }

    /** @test */
    public function it_checks_if_it_exists_on_the_marketplace()
    {
        $this->assertTrue($this->license(['exists' => true])->existsOnMarketplace());
        $this->assertFalse($this->license(['exists' => false])->existsOnMarketplace());
    }
}

class FakeAddonLicenseAddon
{
    protected $name;
    protected $version;

    public function __construct($name, $version)
    {
        $this->name = $name;
        $this->version = $version;
    }

    public function name()
    {
        return $this->name;
    }

    public function version()
    {
        return $this->version;
    }
}

