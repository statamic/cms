<?php

namespace Tests\OAuth;

use Laravel\Socialite\Facades\Socialite;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User as UserFacade;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class OAuthLoginTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function defineEnvironment($app)
    {
        $app['config']->set('statamic.oauth.enabled', true);
        $app['config']->set('statamic.oauth.providers', ['test' => 'Test']);

        // Give the control panel a distinct guard so the referer-based guard
        // selection is actually observable (both default to "web" otherwise).
        $app['config']->set('statamic.users.guards.cp', 'cp');
        $app['config']->set('statamic.users.guards.web', 'web');
    }

    #[Test]
    public function it_redirects_to_the_provider()
    {
        Socialite::fake('test');

        $this->get(route('statamic.oauth.login', 'test'))
            ->assertRedirect('https://socialite.fake/test/authorize');
    }

    #[Test]
    public function a_front_end_request_stores_the_web_guard()
    {
        Socialite::fake('test');

        $this->get(route('statamic.oauth.login', 'test'))
            ->assertSessionHas('statamic.oauth.guard', 'web');
    }

    #[Test]
    public function a_control_panel_request_stores_the_cp_guard()
    {
        Socialite::fake('test');

        $this->get(route('statamic.oauth.login', 'test'), ['referer' => 'http://localhost/cp/dashboard'])
            ->assertSessionHas('statamic.oauth.guard', 'cp');
    }

    #[Test]
    public function it_404s_for_an_unknown_provider()
    {
        $this->get(route('statamic.oauth.login', 'unknown'))->assertNotFound();
    }

    #[Test]
    public function a_logged_in_user_is_redirected_to_the_provider_to_connect()
    {
        Socialite::fake('test');

        $user = UserFacade::make()->id('user-1')->email('one@example.com')->save();

        $this->actingAs($user)
            ->get(route('statamic.oauth.login', 'test'))
            ->assertRedirect('https://socialite.fake/test/authorize');
    }
}
