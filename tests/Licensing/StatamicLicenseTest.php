<?php

namespace Tests\Licensing;

use Facades\Statamic\Version;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function it_checks_if_its_pro()
    {
        $license = $this->license();

        config(['statamic.editions.pro' => true]);
        $this->assertTrue($license->pro());

        config(['statamic.editions.pro' => false]);
        $this->assertFalse($license->pro());
    }

    #[Test]
    public function it_gets_the_version()
    {
        Version::shouldReceive('get')->twice()->andReturn('3.4.5', '6.7.8');

        $license = $this->license();

        $this->assertEquals('3.4.5', $license->version());
        $this->assertEquals('6.7.8', $license->version());
    }

    #[Test]
    public function it_gets_the_invalid_reason_for_a_range_issue()
    {
        $license = $this->license([
            'reason' => 'outside_license_range',
            'range' => ['2', '4'],
        ]);

        $key = 'statamic::messages.licensing_error_outside_license_range';
        $message = trans($key, ['start' => '2', 'end' => '4']);
        $this->assertNotEquals($key, $message);
        $this->assertEquals($message, $license->invalidReason());
    }

    #[Test]
    public function it_needs_renewal_if_outside_license_range()
    {
        $license = $this->license(['valid' => true]);
        $this->assertFalse($license->needsRenewal());

        $license = $this->license(['valid' => false, 'reason' => 'unlicensed']);
        $this->assertFalse($license->needsRenewal());

        $license = $this->license(['valid' => false, 'reason' => 'outside_license_range']);
        $this->assertTrue($license->needsRenewal());
    }
}
