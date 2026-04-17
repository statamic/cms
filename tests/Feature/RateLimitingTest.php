<?php

namespace Tests\Feature;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function assertNotRateLimited($response): void
    {
        $this->assertNotEquals(429, $response->getStatusCode(), 'Request was unexpectedly rate limited.');
    }

    #[Test]
    public function login_endpoint_is_rate_limited()
    {
        collect(range(1, 4))->each(fn () => $this->assertNotRateLimited($this->post('/!/auth/login')));
        $this->post('/!/auth/login')->assertStatus(429);
    }

    #[Test]
    public function register_endpoint_is_rate_limited()
    {
        collect(range(1, 4))->each(fn () => $this->assertNotRateLimited($this->post('/!/auth/register')));
        $this->post('/!/auth/register')->assertStatus(429);
    }

    #[Test]
    public function password_email_endpoint_is_rate_limited()
    {
        collect(range(1, 4))->each(fn () => $this->assertNotRateLimited($this->post('/!/auth/password/email')));
        $this->post('/!/auth/password/email')->assertStatus(429);
    }

    #[Test]
    public function password_reset_endpoint_is_rate_limited()
    {
        collect(range(1, 4))->each(fn () => $this->assertNotRateLimited($this->post('/!/auth/password/reset')));
        $this->post('/!/auth/password/reset')->assertStatus(429);
    }

    #[Test]
    public function forms_endpoint_is_rate_limited()
    {
        collect(range(1, 10))->each(fn () => $this->assertNotRateLimited($this->post('/!/forms/contact')));
        $this->post('/!/forms/contact')->assertStatus(429);
    }

    #[Test]
    public function auth_rate_limiter_can_be_overridden()
    {
        // Simulate a developer overriding the default 4/min limit to 2/min
        RateLimiter::for('statamic.auth', fn ($request) => Limit::perMinute(2)->by($request->ip()));

        $this->assertNotRateLimited($this->post('/!/auth/login'));
        $this->assertNotRateLimited($this->post('/!/auth/login'));
        $this->post('/!/auth/login')->assertStatus(429);
    }

    #[Test]
    public function forms_rate_limiter_can_be_overridden()
    {
        // Simulate a developer overriding the default 10/min limit to 2/min
        RateLimiter::for('statamic.forms', fn ($request) => Limit::perMinute(2)->by($request->ip()));

        $this->assertNotRateLimited($this->post('/!/forms/contact'));
        $this->assertNotRateLimited($this->post('/!/forms/contact'));
        $this->post('/!/forms/contact')->assertStatus(429);
    }
}
