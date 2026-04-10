<?php

namespace Tests\Routing;

use Illuminate\Routing\Middleware\ThrottleRequests;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class RouteMiddlewareTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function withAuthThrottleMiddleware($app)
    {
        $app['config']->set('statamic.routes.auth_middleware', [ThrottleRequests::class.':2,1']);
    }

    protected function withFormsThrottleMiddleware($app)
    {
        $app['config']->set('statamic.routes.forms_middleware', [ThrottleRequests::class.':2,1']);
    }

    #[Test]
    public function no_extra_middleware_is_applied_to_auth_routes_by_default()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/!/auth/login', ['email' => 'test@example.com', 'password' => 'wrong'])
                ->assertStatus(302);
        }
    }

    #[Test]
    #[DefineEnvironment('withAuthThrottleMiddleware')]
    public function custom_middleware_is_applied_to_auth_login_route()
    {
        $this->post('/!/auth/login', ['email' => 'test@example.com', 'password' => 'wrong'])->assertStatus(302);
        $this->post('/!/auth/login', ['email' => 'test@example.com', 'password' => 'wrong'])->assertStatus(302);
        $this->post('/!/auth/login', ['email' => 'test@example.com', 'password' => 'wrong'])->assertStatus(429);
    }

    #[Test]
    #[DefineEnvironment('withAuthThrottleMiddleware')]
    public function custom_auth_middleware_is_applied_to_all_auth_routes()
    {
        $this->post('/!/auth/password/email', ['email' => 'test@example.com'])->assertStatus(302);
        $this->post('/!/auth/password/email', ['email' => 'test@example.com'])->assertStatus(302);
        $this->post('/!/auth/password/email', ['email' => 'test@example.com'])->assertStatus(429);
    }

    #[Test]
    #[DefineEnvironment('withAuthThrottleMiddleware')]
    public function custom_auth_middleware_does_not_affect_forms_route()
    {
        $this->createContactForm();

        // Auth routes reach the throttle limit
        $this->post('/!/auth/login', ['email' => 'test@example.com', 'password' => 'wrong'])->assertStatus(302);
        $this->post('/!/auth/login', ['email' => 'test@example.com', 'password' => 'wrong'])->assertStatus(302);
        $this->post('/!/auth/login', ['email' => 'test@example.com', 'password' => 'wrong'])->assertStatus(429);

        // Forms route is unaffected
        $this->post('/!/forms/contact', ['email' => 'test@example.com'])->assertStatus(302);
        $this->post('/!/forms/contact', ['email' => 'test@example.com'])->assertStatus(302);
        $this->post('/!/forms/contact', ['email' => 'test@example.com'])->assertStatus(302);
    }

    #[Test]
    public function no_extra_middleware_is_applied_to_forms_route_by_default()
    {
        $this->createContactForm();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/!/forms/contact', ['email' => 'test@example.com'])->assertStatus(302);
        }
    }

    #[Test]
    #[DefineEnvironment('withFormsThrottleMiddleware')]
    public function custom_middleware_is_applied_to_forms_route()
    {
        $this->createContactForm();

        $this->post('/!/forms/contact', ['email' => 'test@example.com'])->assertStatus(302);
        $this->post('/!/forms/contact', ['email' => 'test@example.com'])->assertStatus(302);
        $this->post('/!/forms/contact', ['email' => 'test@example.com'])->assertStatus(429);
    }

    #[Test]
    #[DefineEnvironment('withFormsThrottleMiddleware')]
    public function custom_forms_middleware_does_not_affect_auth_routes()
    {
        $this->createContactForm();

        // Forms route reaches the throttle limit
        $this->post('/!/forms/contact', ['email' => 'test@example.com'])->assertStatus(302);
        $this->post('/!/forms/contact', ['email' => 'test@example.com'])->assertStatus(302);
        $this->post('/!/forms/contact', ['email' => 'test@example.com'])->assertStatus(429);

        // Auth routes are unaffected
        $this->post('/!/auth/login', ['email' => 'test@example.com', 'password' => 'wrong'])->assertStatus(302);
        $this->post('/!/auth/login', ['email' => 'test@example.com', 'password' => 'wrong'])->assertStatus(302);
        $this->post('/!/auth/login', ['email' => 'test@example.com', 'password' => 'wrong'])->assertStatus(302);
    }

    private function createContactForm(): void
    {
        $blueprint = Blueprint::make()->setContents([
            'fields' => [
                ['handle' => 'email', 'field' => ['type' => 'text', 'validate' => 'required|email']],
            ],
        ]);

        Blueprint::shouldReceive('find')->with('forms.contact')->andReturn($blueprint);
        Blueprint::makePartial();

        $form = Form::make()->handle('contact');
        Form::shouldReceive('find')->with('contact')->andReturn($form);
        Form::makePartial();
    }
}
