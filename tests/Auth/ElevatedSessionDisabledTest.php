<?php

namespace Tests\Auth;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('elevated-session')]
class ElevatedSessionDisabledTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::make()->email('foo@bar.com')->makeSuper()->password('secret');
        $this->user->save();
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('statamic.users.elevated_sessions_enabled', false);
    }

    #[Test]
    public function cp_elevated_session_routes_are_not_registered()
    {
        $this->actingAs($this->user);

        $this->get('/cp/elevated-session')->assertNotFound();
        $this->get('/cp/elevated-session/passkey-options')->assertNotFound();
        $this->post('/cp/elevated-session')->assertNotFound();
        $this->get('/cp/elevated-session/resend-code')->assertNotFound();
        $this->get('/cp/auth/confirm-password')->assertNotFound();
    }

    #[Test]
    public function frontend_elevated_session_routes_are_not_registered()
    {
        $this->actingAs($this->user);

        $this->get('/!/auth/confirm-password')->assertNotFound();
        $this->post('/!/auth/elevated-session')->assertNotFound();
        $this->get('/!/auth/elevated-session/passkey-options')->assertNotFound();
        $this->get('/!/auth/elevated-session/resend-code')->assertNotFound();
    }
}
