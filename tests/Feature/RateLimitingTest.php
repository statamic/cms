<?php

namespace Tests\Feature;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    #[Test]
    public function login_endpoint_is_rate_limited()
    {
        collect(range(1, 4))->each(fn () => $this->post('/!/auth/login')->assertNotRateLimited());
        $this->post('/!/auth/login')->assertRateLimited();
    }

    #[Test]
    public function register_endpoint_is_rate_limited()
    {
        collect(range(1, 4))->each(fn () => $this->post('/!/auth/register')->assertNotRateLimited());
        $this->post('/!/auth/register')->assertRateLimited();
    }

    #[Test]
    public function password_email_endpoint_is_rate_limited()
    {
        collect(range(1, 4))->each(fn () => $this->post('/!/auth/password/email')->assertNotRateLimited());
        $this->post('/!/auth/password/email')->assertRateLimited();
    }

    #[Test]
    public function password_reset_endpoint_is_rate_limited()
    {
        collect(range(1, 4))->each(fn () => $this->post('/!/auth/password/reset')->assertNotRateLimited());
        $this->post('/!/auth/password/reset')->assertRateLimited();
    }

    #[Test]
    public function forms_endpoint_is_rate_limited()
    {
        collect(range(1, 10))->each(fn () => $this->post('/!/forms/contact')->assertNotRateLimited());
        $this->post('/!/forms/contact')->assertRateLimited();
    }

    #[Test]
    public function cp_login_endpoint_is_rate_limited()
    {
        collect(range(1, 4))->each(fn () => $this->post('/cp/auth/login')->assertNotRateLimited());
        $this->post('/cp/auth/login')->assertRateLimited();
    }

    #[Test]
    public function cp_password_email_endpoint_is_rate_limited()
    {
        collect(range(1, 4))->each(fn () => $this->post('/cp/auth/password/email')->assertNotRateLimited());
        $this->post('/cp/auth/password/email')->assertRateLimited();
    }

    #[Test]
    public function cp_password_reset_endpoint_is_rate_limited()
    {
        collect(range(1, 4))->each(fn () => $this->post('/cp/auth/password/reset')->assertNotRateLimited());
        $this->post('/cp/auth/password/reset')->assertRateLimited();
    }

    #[Test]
    public function cp_and_frontend_auth_have_independent_buckets()
    {
        collect(range(1, 4))->each(fn () => $this->post('/!/auth/login')->assertNotRateLimited());
        $this->post('/!/auth/login')->assertRateLimited();

        $this->post('/cp/auth/login')->assertNotRateLimited();
    }

    #[Test]
    public function auth_rate_limiter_can_be_overridden()
    {
        // Simulate a developer overriding the default 4/min limit to 2/min
        RateLimiter::for('statamic.auth', fn ($request) => Limit::perMinute(2)->by($request->ip()));

        $this->post('/!/auth/login')->assertNotRateLimited();
        $this->post('/!/auth/login')->assertNotRateLimited();
        $this->post('/!/auth/login')->assertRateLimited();
    }

    #[Test]
    public function cp_auth_rate_limiter_inherits_overrides_to_statamic_auth()
    {
        RateLimiter::for('statamic.auth', fn ($request) => Limit::perMinute(2)->by($request->ip()));

        $this->post('/cp/auth/login')->assertNotRateLimited();
        $this->post('/cp/auth/login')->assertNotRateLimited();
        $this->post('/cp/auth/login')->assertRateLimited();
    }

    #[Test]
    public function cp_auth_rate_limiter_can_be_overridden_independently()
    {
        RateLimiter::for('statamic.cp.auth', fn ($request) => Limit::perMinute(2)->by($request->ip()));

        $this->post('/cp/auth/login')->assertNotRateLimited();
        $this->post('/cp/auth/login')->assertNotRateLimited();
        $this->post('/cp/auth/login')->assertRateLimited();

        // Frontend auth still uses the default 4/min
        collect(range(1, 4))->each(fn () => $this->post('/!/auth/login')->assertNotRateLimited());
    }

    #[Test]
    public function forms_rate_limiter_can_be_overridden()
    {
        // Simulate a developer overriding the default 10/min limit to 2/min
        RateLimiter::for('statamic.forms', fn ($request) => Limit::perMinute(2)->by($request->ip()));

        $this->post('/!/forms/contact')->assertNotRateLimited();
        $this->post('/!/forms/contact')->assertNotRateLimited();
        $this->post('/!/forms/contact')->assertRateLimited();
    }

    #[Test]
    public function passkey_endpoint_is_rate_limited()
    {
        collect(range(1, 30))->each(fn () => $this->post('/!/auth/passkeys/auth')->assertNotRateLimited());
        $this->post('/!/auth/passkeys/auth')->assertRateLimited();
    }

    #[Test]
    public function cp_passkey_endpoint_is_rate_limited()
    {
        collect(range(1, 30))->each(fn () => $this->post('/cp/auth/passkeys')->assertNotRateLimited());
        $this->post('/cp/auth/passkeys')->assertRateLimited();
    }

    #[Test]
    public function elevated_session_confirm_endpoint_is_rate_limited()
    {
        $this->actingAs(tap(User::make()->email('foo@bar.com'))->save());

        collect(range(1, 4))->each(fn () => $this->post('/!/auth/elevated-session')->assertNotRateLimited());
        $this->post('/!/auth/elevated-session')->assertRateLimited();
    }

    #[Test]
    public function cp_elevated_session_confirm_endpoint_is_rate_limited()
    {
        $this->actingAs(tap(User::make()->email('foo@bar.com')->makeSuper())->save());

        collect(range(1, 4))->each(fn () => $this->post('/cp/elevated-session')->assertNotRateLimited());
        $this->post('/cp/elevated-session')->assertRateLimited();
    }

    #[Test]
    public function elevated_session_passkey_options_endpoint_is_rate_limited()
    {
        $this->actingAs(tap(User::make()->email('foo@bar.com'))->save());

        collect(range(1, 30))->each(fn () => $this->get('/!/auth/elevated-session/passkey-options')->assertNotRateLimited());
        $this->get('/!/auth/elevated-session/passkey-options')->assertRateLimited();
    }

    #[Test]
    public function cp_elevated_session_passkey_options_endpoint_is_rate_limited()
    {
        $this->actingAs(tap(User::make()->email('foo@bar.com')->makeSuper())->save());

        collect(range(1, 30))->each(fn () => $this->get('/cp/elevated-session/passkey-options')->assertNotRateLimited());
        $this->get('/cp/elevated-session/passkey-options')->assertRateLimited();
    }

    #[Test]
    public function cp_and_frontend_passkeys_have_independent_buckets()
    {
        RateLimiter::for('statamic.passkeys', fn ($request) => Limit::perMinute(2)->by($request->ip()));

        $this->post('/!/auth/passkeys/auth')->assertNotRateLimited();
        $this->post('/!/auth/passkeys/auth')->assertNotRateLimited();
        $this->post('/!/auth/passkeys/auth')->assertRateLimited();

        $this->post('/cp/auth/passkeys')->assertNotRateLimited();
    }

    #[Test]
    public function passkeys_bucket_is_independent_from_auth_bucket()
    {
        collect(range(1, 4))->each(fn () => $this->post('/!/auth/login')->assertNotRateLimited());
        $this->post('/!/auth/login')->assertRateLimited();

        $this->post('/!/auth/passkeys/auth')->assertNotRateLimited();
    }

    #[Test]
    public function passkeys_rate_limiter_can_be_overridden()
    {
        RateLimiter::for('statamic.passkeys', fn ($request) => Limit::perMinute(2)->by($request->ip()));

        $this->post('/!/auth/passkeys/auth')->assertNotRateLimited();
        $this->post('/!/auth/passkeys/auth')->assertNotRateLimited();
        $this->post('/!/auth/passkeys/auth')->assertRateLimited();
    }

    #[Test]
    public function cp_passkeys_rate_limiter_inherits_overrides_to_statamic_passkeys()
    {
        RateLimiter::for('statamic.passkeys', fn ($request) => Limit::perMinute(2)->by($request->ip()));

        $this->post('/cp/auth/passkeys')->assertNotRateLimited();
        $this->post('/cp/auth/passkeys')->assertNotRateLimited();
        $this->post('/cp/auth/passkeys')->assertRateLimited();
    }

    #[Test]
    public function cp_passkeys_rate_limiter_can_be_overridden_independently()
    {
        RateLimiter::for('statamic.cp.passkeys', fn ($request) => Limit::perMinute(2)->by($request->ip()));

        $this->post('/cp/auth/passkeys')->assertNotRateLimited();
        $this->post('/cp/auth/passkeys')->assertNotRateLimited();
        $this->post('/cp/auth/passkeys')->assertRateLimited();

        // Frontend passkey still uses the default 30/min
        collect(range(1, 30))->each(fn () => $this->post('/!/auth/passkeys/auth')->assertNotRateLimited());
    }
}
