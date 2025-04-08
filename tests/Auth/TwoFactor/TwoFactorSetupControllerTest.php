<?php

namespace Tests\Auth\TwoFactor;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\Google2FA;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TwoFactorSetupControllerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('statamic.users.two_factor.enabled', true);
    }

    #[Test]
    public function it_shows_the_two_factor_setup_view()
    {
        $this
            ->actingAs($this->user())
            ->get(cp_route('two-factor.setup'))
            ->assertViewIs('statamic::auth.two-factor.setup');
    }

    #[Test]
    public function it_redirects_to_the_dashboard_if_the_user_is_already_set_up()
    {
        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->get(cp_route('two-factor.setup'))
            ->assertRedirect(cp_route('index'));
    }

    #[Test]
    public function it_has_the_correct_cancellable_property()
    {
        $user = $this->user();

        $this
            ->actingAs($user)
            ->get(cp_route('two-factor.setup'), [
                'cancellable' => false,
            ])
            ->assertViewHas('cancellable', false);

        // No roles enforced
        config()->set('statamic.users.two_factor.enforced_roles', []);

        $role = Role::make('enforceable_role')
            ->addPermission('access cp')
            ->save();

        $user
            ->set('super', false)
            ->assignRole($role)
            ->save();

        $this
            ->actingAs($user)
            ->get(cp_route('two-factor.setup'))
            ->assertViewHas('cancellable', true);
    }

    #[Test]
    public function it_shows_the_recovery_codes()
    {
        $user = $this->user();

        $user->set('two_factor_secret', encrypt(app(Google2FA::class)->generateSecretKey()));
        $user->set('two_factor_recovery_codes', encrypt(json_encode(Collection::times(8, function () {
            return RecoveryCode::generate();
        })->all())));

        $this
            ->actingAs($user)
            ->post(cp_route('two-factor.confirm'), [
                'code' => $this->getOneTimeCode(),
            ])
            ->assertViewIs('statamic::auth.two-factor.recovery-codes')
            ->assertViewHas('recovery_codes');
    }

    #[Test]
    public function it_completes_setup_and_redirects()
    {
        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->post(cp_route('two-factor.complete'), [
                'code' => $this->getOneTimeCode(),
            ])
            ->assertRedirect(cp_route('index'));
    }

    private function user()
    {
        return tap(User::make()->makeSuper())->save();
    }

    private function userWithTwoFactorEnabled()
    {
        $user = $this->user();

        $user->merge([
            'two_factor_locked' => false,
            'two_factor_confirmed_at' => now(),
            'two_factor_completed' => now(),
            'two_factor_secret' => encrypt(app(Google2FA::class)->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ]);

        $user->save();

        return $user;
    }

    private function getOneTimeCode()
    {
        $provider = app(Google2FA::class);

        // get a one-time code (so we can make sure we have a wrong one in the test)
        $internalProvider = app(\PragmaRX\Google2FA\Google2FA::class);

        return $internalProvider->getCurrentOtp($provider->getSecretKey());
    }
}
