<?php

namespace Tests\Licensing;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Addon;
use Statamic\Licensing\AddonLicense;
use Tests\TestCase;

class AddonLicenseTest extends TestCase
{
    use LicenseTests;

    protected function license($response = [])
    {
        Addon::shouldReceive('get')->with('test/addon')
            ->zeroOrMoreTimes()
            ->andReturn(new FakeAddonLicenseAddon('Test Addon', '1.2.3', 'rad'));
        $this->addToAssertionCount(-1); // dont need to assert this.

        return new AddonLicense('test/addon', $response);
    }

    #[Test]
    public function it_gets_the_addons_name()
    {
        $this->assertEquals($this->license()->name(), 'Test Addon');
    }

    #[Test]
    public function it_gets_the_addons_version()
    {
        $this->assertEquals($this->license()->version(), '1.2.3');
    }

    #[Test]
    public function it_gets_the_addons_edition()
    {
        $this->assertEquals($this->license()->edition(), 'rad');
    }

    #[Test]
    public function it_checks_if_it_exists_on_the_marketplace()
    {
        $this->assertTrue($this->license(['exists' => true])->existsOnMarketplace());
        $this->assertFalse($this->license(['exists' => false])->existsOnMarketplace());
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
    public function it_gets_the_invalid_reason_for_a_edition_issue()
    {
        $license = $this->license([
            'reason' => 'invalid_edition',
            'edition' => 'one',
        ]);

        $key = 'statamic::messages.licensing_error_invalid_edition';
        $message = trans($key, ['edition' => 'one']);
        $this->assertNotEquals($key, $message);
        $this->assertEquals($message, $license->invalidReason());
    }

    #[Test]
    public function it_gets_the_version_limit()
    {
        $license = $this->license(['version_limit' => 4]);

        $this->assertEquals(4, $license->versionLimit());
    }
}

class FakeAddonLicenseAddon
{
    protected $name;
    protected $version;
    protected $edition;

    public function __construct($name, $version, $edition)
    {
        $this->name = $name;
        $this->version = $version;
        $this->edition = $edition;
    }

    public function name()
    {
        return $this->name;
    }

    public function version()
    {
        return $this->version;
    }

    public function edition()
    {
        return $this->edition;
    }
}
