<?php

namespace Tests\Feature\Users;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Contracts\Auth\TwoFactor\TwoFactorAuthenticationProvider;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('2fa')]
class TwoFactorSetupTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_shows_the_two_factor_setup_view()
    {
        $this
            ->actingAs($this->user())
            ->get(cp_route('two-factor-setup'))
            ->assertInertia(fn ($page) => $page->component('auth/two-factor/Setup'));
    }

    #[Test]
    public function it_redirects_to_the_dashboard_if_the_user_is_already_set_up()
    {
        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->get(cp_route('two-factor-setup'))
            ->assertRedirect(cp_route('index'));
    }

    #[Test]
    public function redirect_url_is_intended_url()
    {
        $this
            ->actingAs($this->user())
            ->withSession(['url.intended' => 'http://localhost/cp/collections'])
            ->get(cp_route('two-factor-setup'))
            ->assertInertia(fn ($page) => $page->where('redirect', 'http://localhost/cp/collections'));
    }

    #[Test]
    public function redirect_url_is_preserved_across_refreshes_of_the_frontend_setup_page()
    {
        $user = $this->user();

        $this
            ->actingAs($user)
            ->withSession(['url.intended' => '/dashboard'])
            ->get(route('statamic.two-factor-setup'))
            ->assertInertia(fn ($page) => $page->where('redirect', '/dashboard'));

        $this
            ->actingAs($user)
            ->get(route('statamic.two-factor-setup'))
            ->assertInertia(fn ($page) => $page->where('redirect', '/dashboard'));
    }

    #[Test]
    public function it_does_not_redirect_to_external_url_on_frontend_route()
    {
        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->get(route('statamic.two-factor-setup', [
                'redirect' => 'https://evil.com',
            ]))
            ->assertRedirect(route('statamic.site'));
    }

    private function user()
    {
        return tap(User::make()->makeSuper())->save();
    }

    private function userWithTwoFactorEnabled()
    {
        $user = $this->user();

        $user->merge([
            'two_factor_confirmed_at' => now()->timestamp,
            'two_factor_secret' => encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ]);

        $user->save();

        return $user;
    }
}
