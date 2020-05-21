<?php

namespace Tests\Licensing;

use Facades\Statamic\Licensing\Pro;
use Facades\Statamic\Version;
use Statamic\Licensing\StatamicLicense;
use Tests\TestCase;

class StatamicLicenseTest extends TestCase
{
    use LicenseTests;

    protected $shouldFakeVersion = false;

    protected function license($response = [])
    {
        return new StatamicLicense($response);
    }

    /** @test */
    public function it_checks_if_its_pro()
    {
        Pro::shouldReceive('check')->twice()->andReturn(true, false);

        $license = $this->license();

        $this->assertTrue($license->pro());
        $this->assertFalse($license->pro());
    }

    /** @test */
    public function it_gets_the_version()
    {
        Version::shouldReceive('get')->twice()->andReturn('3.4.5', '6.7.8');

        $license = $this->license();

        $this->assertEquals('3.4.5', $license->version());
        $this->assertEquals('6.7.8', $license->version());
    }
}
