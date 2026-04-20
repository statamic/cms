<?php

namespace Tests\Feature;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
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
    public function auth_rate_limiter_can_be_overridden()
    {
        // Simulate a developer overriding the default 4/min limit to 2/min
        RateLimiter::for('statamic.auth', fn ($request) => Limit::perMinute(2)->by($request->ip()));

        $this->post('/!/auth/login')->assertNotRateLimited();
        $this->post('/!/auth/login')->assertNotRateLimited();
        $this->post('/!/auth/login')->assertRateLimited();
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
}
