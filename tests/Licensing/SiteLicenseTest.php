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
    public function it_prefers_the_legacy_license_key_over_the_site_key()
    {
        config([
            'statamic.system.site_key' => 'site_abcdefghijklmnopqrstuvwxyz',
            'statamic.system.license_key' => 'aRadLicenseKey42',
        ]);

        $this->assertEquals('aRadLicenseKey42', $this->license()->key());
    }

    #[Test]
    public function it_uses_the_site_key_when_there_is_no_legacy_license_key()
    {
        config([
            'statamic.system.site_key' => 'site_abcdefghijklmnopqrstuvwxyz',
            'statamic.system.license_key' => null,
        ]);

        $this->assertEquals('site_abcdefghijklmnopqrstuvwxyz', $this->license()->key());
    }

    #[Test]
    public function it_falls_back_to_the_legacy_license_key()
    {
        config([
            'statamic.system.site_key' => null,
            'statamic.system.license_key' => 'aRadLicenseKey42',
        ]);

        $this->assertEquals('aRadLicenseKey42', $this->license()->key());
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
    public function it_accepts_the_site_key_format()
    {
        config(['statamic.system.site_key' => 'site_abcdefghijklmnopqrstuvwxyz']);

        $this->assertFalse($this->license()->usesIncorrectKeyFormat());
    }

    #[Test]
    public function it_rejects_a_truncated_site_key()
    {
        config(['statamic.system.site_key' => 'site_tooshort']);

        $this->assertTrue($this->license()->usesIncorrectKeyFormat());
    }

    #[Test]
    public function it_does_not_flag_a_missing_key_as_incorrect_format()
    {
        config([
            'statamic.system.site_key' => null,
            'statamic.system.license_key' => null,
        ]);

        $this->assertFalse($this->license()->usesIncorrectKeyFormat());
    }

    #[Test]
    public function it_gets_the_url_with_a_key()
    {
        config(['statamic.system.license_key' => 'test-key']);

        $this->assertEquals('https://statamic.com/account/sites/test-key', $this->license()->url());
        $this->assertEquals('https://statamic.com/account/licensing/handoff?'.http_build_query([
            'key' => 'test-key',
            'name' => config('app.name'),
            'return' => url(cp_route('utilities.licensing')),
        ]), $this->license()->handoffUrl());
    }

    #[Test]
    public function it_gets_the_edit_url_without_a_key()
    {
        $this->assertEquals('https://statamic.com/account/sites/create', $this->license()->url());
        $this->assertNull($this->license()->handoffUrl());
    }

    #[Test]
    public function it_gets_the_registered_site_name()
    {
        $this->assertNull($this->license()->name());
        $this->assertEquals('Wayne Enterprises', $this->license(['name' => 'Wayne Enterprises'])->name());
    }

    #[Test]
    public function it_knows_whether_the_site_is_connected()
    {
        $this->assertFalse($this->license()->isConnected());
        $this->assertFalse($this->license(['claimed' => false])->isConnected());
        $this->assertTrue($this->license(['claimed' => true])->isConnected());
    }

    #[Test]
    public function it_hides_missing_domains_until_the_site_is_linked()
    {
        $unlinked = $this->license(['valid' => false, 'reason' => 'no_domains', 'claimed' => false]);

        $this->assertTrue($unlinked->valid());
        $this->assertNull($unlinked->invalidReason());

        $linked = $this->license(['valid' => false, 'reason' => 'no_domains', 'claimed' => true]);

        $this->assertFalse($linked->valid());
        $this->assertEquals(__('statamic::messages.licensing_error_no_domains'), $linked->invalidReason());
    }

    #[Test]
    public function it_knows_whether_the_domain_is_invalid()
    {
        $this->assertFalse($this->license()->hasInvalidDomain());
        $this->assertTrue($this->license(['reason' => 'invalid_domain'])->hasInvalidDomain());
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
