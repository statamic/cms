<?php

namespace Tests\Licensing;

use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Licensing\AddonLicense;
use Statamic\Licensing\LicenseManager;
use Statamic\Licensing\Outpost;
use Statamic\Licensing\SiteLicense;
use Statamic\Licensing\StatamicLicense;
use Tests\TestCase;

class LicenseManagerTest extends TestCase
{
    #[Test]
    public function it_gets_the_outpost_response()
    {
        $manager = $this->managerWithResponse(['the' => 'response']);

        $this->assertEquals(['the' => 'response'], $manager->response());
    }

    #[Test]
    public function it_clears_the_outpost_response()
    {
        $outpost = $this->mock(Outpost::class);
        $outpost->shouldReceive('clearCachedResponse')->once();

        (new LicenseManager($outpost))->refresh();
    }

    #[Test]
    public function it_checks_for_public_domains()
    {
        $this->assertTrue($this->managerWithResponse(['public' => true])->isOnPublicDomain());
        $this->assertFalse($this->managerWithResponse(['public' => false])->isOnPublicDomain());
    }

    #[Test]
    public function it_checks_for_test_domains()
    {
        $this->assertFalse($this->managerWithResponse(['public' => true])->isOnTestDomain());
        $this->assertTrue($this->managerWithResponse(['public' => false])->isOnTestDomain());
    }

    #[Test]
    public function licenses_are_valid_if_statamic_and_all_addons_are_valid()
    {
        $licenses = $this->managerWithResponse([
            'statamic' => ['valid' => true],
            'packages' => [
                'foo/bar' => ['valid' => true],
                'baz/qux' => ['valid' => true],
            ],
        ]);

        $this->assertTrue($licenses->valid());
        $this->assertFalse($licenses->invalid());
    }

    #[Test]
    public function licenses_are_invalid_if_statamic_is_invalid_but_addons_are_valid()
    {
        $licenses = $this->managerWithResponse([
            'statamic' => ['valid' => false],
            'packages' => [
                'foo/bar' => ['valid' => true],
                'baz/qux' => ['valid' => true],
            ],
        ]);

        $this->assertFalse($licenses->valid());
        $this->assertTrue($licenses->invalid());
    }

    #[Test]
    public function licenses_are_invalid_if_statamic_is_valid_but_any_addons_are_invalid()
    {
        $licenses = $this->managerWithResponse([
            'statamic' => ['valid' => true],
            'packages' => [
                'foo/bar' => ['valid' => true],
                'baz/qux' => ['valid' => false],
            ],
        ]);

        $this->assertFalse($licenses->valid());
        $this->assertTrue($licenses->invalid());
    }

    #[Test]
    public function it_gets_the_site_license()
    {
        $licenses = $this->managerWithResponse(['site' => 'test-response']);

        $site = $licenses->site();

        $this->assertInstanceOf(SiteLicense::class, $site);
        $this->assertEquals('test-response', $site->response());
    }

    #[Test]
    public function it_gets_the_statamic_license()
    {
        $licenses = $this->managerWithResponse(['statamic' => 'test-response']);

        $statamic = $licenses->statamic();

        $this->assertInstanceOf(StatamicLicense::class, $statamic);
        $this->assertEquals('test-response', $statamic->response());
    }

    #[Test]
    public function it_gets_the_addon_licenses()
    {
        $licenses = $this->managerWithResponse([
            'packages' => [
                'foo/bar' => 'the foo/bar response',
                'baz/qux' => 'the baz/qux response',
            ],
        ]);

        $addons = $licenses->addons();

        $this->assertInstanceOf(Collection::class, $addons);
        $this->assertEveryItemIsInstanceOf(AddonLicense::class, $addons);
        $this->assertEquals(['foo/bar', 'baz/qux'], $addons->keys()->all());
        $this->assertEquals('the foo/bar response', $addons['foo/bar']->response());
        $this->assertEquals('the baz/qux response', $addons['baz/qux']->response());
    }

    #[Test]
    public function it_checks_if_statamic_license_needs_renewal()
    {
        $this->assertFalse($this->managerWithResponse([
            'statamic' => ['valid' => true],
        ])->statamicNeedsRenewal());

        $this->assertFalse($this->managerWithResponse([
            'statamic' => ['valid' => false, 'reason' => 'unlicensed'],
        ])->statamicNeedsRenewal());

        $this->assertTrue($this->managerWithResponse([
            'statamic' => ['valid' => false, 'reason' => 'outside_license_range'],
        ])->statamicNeedsRenewal());
    }

    #[Test]
    public function it_checks_for_request_failures()
    {
        Carbon::setTestNow(now()->startOfMinute());

        tap($this->managerWithResponse(['error' => 500]), function ($licenses) {
            $this->assertTrue($licenses->requestFailed());
            $this->assertEquals(500, $licenses->requestErrorCode());
            $this->assertFalse($licenses->requestRateLimited());
            $this->assertNull($licenses->failedRequestRetrySeconds());
            $this->assertInstanceOf(MessageBag::class, $licenses->requestValidationErrors());
            $this->assertEquals([], $licenses->requestValidationErrors()->all());
        });

        tap($this->managerWithResponse([
            'error' => 422,
            'errors' => ['foo' => ['one'], 'bar' => ['two']],
        ]), function ($licenses) {
            $this->assertTrue($licenses->requestFailed());
            $this->assertEquals(422, $licenses->requestErrorCode());
            $this->assertFalse($licenses->requestRateLimited());
            $this->assertNull($licenses->failedRequestRetrySeconds());
            $this->assertInstanceOf(MessageBag::class, $licenses->requestValidationErrors());
            $this->assertEquals(['one', 'two'], $licenses->requestValidationErrors()->all());
        });

        tap($this->managerWithResponse([
            'error' => 429,
            'expiry' => now()->addSeconds(10)->timestamp,
        ]), function ($licenses) {
            $this->assertTrue($licenses->requestFailed());
            $this->assertEquals(429, $licenses->requestErrorCode());
            $this->assertTrue($licenses->requestRateLimited());
            $this->assertEquals(10, $licenses->failedRequestRetrySeconds());
            $this->assertInstanceOf(MessageBag::class, $licenses->requestValidationErrors());
            $this->assertEquals([], $licenses->requestValidationErrors()->all());
        });
    }

    #[Test]
    public function licensing_alert_distinguishes_identity_from_license()
    {
        config(['statamic.system.site_key' => 'site_abcdefghijklmnopqrstuvwxyz']);

        $alert = $this->managerWithResponse([
            'public' => false,
            'statamic' => ['valid' => false, 'reason' => 'unlicensed'],
            'packages' => [],
        ])->licensingAlert();

        $this->assertTrue($alert['testing']);
        $this->assertTrue($alert['hasSiteKey']);
        $this->assertStringContainsString('isn\'t connected to a statamic.com account', $alert['message']);
        $this->assertStringContainsString('trial mode', $alert['message']);
        $this->assertEquals('connect', $this->managerWithResponse([
            'public' => false,
            'site' => ['claimed' => false],
            'statamic' => ['valid' => false, 'reason' => 'unlicensed'],
            'packages' => [],
        ])->primaryAction());
    }

    #[Test]
    public function licensing_alert_notes_a_missing_site_key()
    {
        config([
            'statamic.system.site_key' => null,
            'statamic.system.license_key' => null,
        ]);

        $alert = $this->managerWithResponse([
            'public' => true,
            'statamic' => ['valid' => false, 'reason' => 'unlicensed'],
            'packages' => [],
        ])->licensingAlert();

        $this->assertFalse($alert['hasSiteKey']);
        $this->assertFalse($alert['sharedKey']);
        $this->assertStringContainsString('does not have a site key', $alert['message']);
        $this->assertStringNotContainsString('site:fresh-key', $alert['message']);
    }

    #[Test]
    public function licensing_alert_notes_a_connected_site_without_a_license()
    {
        config(['statamic.system.site_key' => 'site_abcdefghijklmnopqrstuvwxyz']);

        $alert = $this->managerWithResponse([
            'public' => true,
            'site' => ['claimed' => true],
            'statamic' => ['valid' => false, 'reason' => 'unlicensed'],
            'packages' => [],
        ])->licensingAlert();

        $this->assertStringContainsString('connected to statamic.com but has no license', $alert['message']);
        $this->assertStringNotContainsString('php please license', $alert['message']);
    }

    #[Test]
    public function primary_action_follows_connection_state()
    {
        config([
            'statamic.system.site_key' => null,
            'statamic.system.license_key' => null,
        ]);

        $this->assertEquals('mint', $this->managerWithResponse([
            'statamic' => ['valid' => true],
            'packages' => [],
        ])->primaryAction());

        config(['statamic.system.site_key' => 'site_abcdefghijklmnopqrstuvwxyz']);

        $this->assertEquals('connect', $this->managerWithResponse([
            'site' => ['claimed' => false],
            'statamic' => ['valid' => false],
            'packages' => [],
        ])->primaryAction());

        $this->assertEquals('domain', $this->managerWithResponse([
            'site' => ['claimed' => true, 'reason' => 'invalid_domain', 'valid' => false],
            'statamic' => ['valid' => false],
            'packages' => [],
        ])->primaryAction());

        $this->assertEquals('buy', $this->managerWithResponse([
            'site' => ['claimed' => true, 'valid' => true],
            'statamic' => ['valid' => false, 'reason' => 'unlicensed'],
            'packages' => [],
        ])->primaryAction());

        $this->assertNull($this->managerWithResponse([
            'site' => ['claimed' => true, 'valid' => true],
            'statamic' => ['valid' => true],
            'packages' => [],
        ])->primaryAction());
    }

    #[Test]
    public function licensing_alert_suggests_fresh_key_only_when_shared()
    {
        config(['statamic.system.site_key' => 'site_abcdefghijklmnopqrstuvwxyz']);

        $alert = $this->managerWithResponse([
            'public' => true,
            'site' => ['valid' => true, 'shared_key' => true],
            'statamic' => ['valid' => false, 'reason' => 'unlicensed'],
            'packages' => [],
        ])->licensingAlert();

        $this->assertTrue($alert['sharedKey']);
        $this->assertStringContainsString('php please site:fresh-key', $alert['message']);
    }

    private function managerWithResponse(array $response)
    {
        $outpost = $this->mock(Outpost::class);

        $outpost->shouldReceive('response')->andReturn($response);

        return new LicenseManager($outpost);
    }
}
