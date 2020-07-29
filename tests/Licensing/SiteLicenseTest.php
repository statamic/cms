<?php

namespace Tests\Licensing;

use Statamic\Licensing\SiteLicense;
use Tests\TestCase;

class SiteLicenseTest extends TestCase
{
    use LicenseTests;

    protected function license($response = [])
    {
        return new SiteLicense($response);
    }

    /** @test */
    public function it_gets_the_key()
    {
        config(['statamic.system.license_key' => 'test-key']);

        $this->assertEquals('test-key', $this->license()->key());
    }

    /** @test */
    public function it_gets_the_url_with_a_key()
    {
        config(['statamic.system.license_key' => 'test-key']);

        $this->assertEquals('https://statamic.com/account/sites/test-key', $this->license()->url());
    }

    /** @test */
    public function it_gets_the_edit_url_without_a_key()
    {
        $this->assertEquals('https://statamic.com/account/sites/create', $this->license()->url());
    }

    /** @test */
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
