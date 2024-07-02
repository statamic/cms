<?php

namespace Tests\Licensing;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Licensing\SiteLicense;
use Tests\TestCase;

class SiteLicenseTest extends TestCase
{
    use LicenseTests;

    protected function license($response = [])
    {
        return new SiteLicense($response);
    }

    #[Test]
    public function it_gets_the_key()
    {
        config(['statamic.system.license_key' => 'test-key']);

        $this->assertEquals('test-key', $this->license()->key());
    }

    #[Test]
    public function it_checks_for_incorrect_key_format()
    {
        config(['statamic.system.license_key' => 'test-key']);

        $this->assertTrue($this->license()->usesIncorrectKeyFormat());
    }

    #[Test]
    public function it_checks_for_correct_key_format()
    {
        config(['statamic.system.license_key' => 'aRadLicenseKey42']);

        $this->assertFalse($this->license()->usesIncorrectKeyFormat());
    }

    #[Test]
    public function it_gets_the_url_with_a_key()
    {
        config(['statamic.system.license_key' => 'test-key']);

        $this->assertEquals('https://statamic.com/account/sites/test-key', $this->license()->url());
    }

    #[Test]
    public function it_gets_the_edit_url_without_a_key()
    {
        $this->assertEquals('https://statamic.com/account/sites/create', $this->license()->url());
    }

    #[Test]
    public function it_gets_domain_information()
    {
        $license = $this->license(['domains' => []]);
        $this->assertFalse($license->hasDomains());
        $this->assertFalse($license->hasMultipleDomains());
        $this->assertEquals(0, $license->additionalDomainCount());
        $this->assertNull($license->domain());
        $this->assertEquals(collect(), $license->domains());

        $license = $this->license(['domains' => [
            ['url' => 'one.com'],
        ]]);
        $this->assertTrue($license->hasDomains());
        $this->assertFalse($license->hasMultipleDomains());
        $this->assertEquals(0, $license->additionalDomainCount());
        $this->assertEquals(['url' => 'one.com'], $license->domain());
        $this->assertEquals(collect([
            ['url' => 'one.com'],
        ]), $license->domains());

        $license = $this->license(['domains' => [
            ['url' => 'one.com'],
            ['url' => 'two.com'],
        ]]);
        $this->assertTrue($license->hasDomains());
        $this->assertTrue($license->hasMultipleDomains());
        $this->assertEquals(1, $license->additionalDomainCount());
        $this->assertEquals(['url' => 'one.com'], $license->domain());
        $this->assertEquals(collect([
            ['url' => 'one.com'],
            ['url' => 'two.com'],
        ]), $license->domains());

        $license = $this->license(['domains' => [
            ['url' => 'one.com'],
            ['url' => 'two.com'],
            ['url' => 'three.com'],
        ]]);
        $this->assertTrue($license->hasDomains());
        $this->assertTrue($license->hasMultipleDomains());
        $this->assertEquals(2, $license->additionalDomainCount());
        $this->assertEquals(['url' => 'one.com'], $license->domain());
        $this->assertEquals(collect([
            ['url' => 'one.com'],
            ['url' => 'two.com'],
            ['url' => 'three.com'],
        ]), $license->domains());
    }
}
