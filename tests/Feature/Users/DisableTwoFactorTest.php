<?php

namespace Feature\Users;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Contracts\Auth\TwoFactor\TwoFactorAuthenticationProvider;
use Statamic\Events\TwoFactorAuthenticationDisabled;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('elevated-session')]
#[Group('2fa')]
class DisableTwoFactorTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_disables_two_factor_authentication_for_the_current_user()
    {
        Event::fake();

        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->withActiveElevatedSession()
            ->delete(cp_route('users.two-factor.disable', [
                'user' => $user->id,
            ]))
            ->assertOk()
            ->assertJson(['redirect' => null]);

        $user->fresh();

        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_secret);

        Event::assertDispatched(TwoFactorAuthenticationDisabled::class, fn ($event) => $event->user->id === $user->id);
    }

    #[Test]
    public function it_disables_two_factor_authentication_for_the_current_user_when_two_factor_is_enforced()
    {
        Event::fake();

        // Enforced for everyone
        config()->set('statamic.users.two_factor_enforced_roles', ['*']);

        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->withActiveElevatedSession()
            ->delete(cp_route('users.two-factor.disable', [
                'user' => $user->id,
            ]))
            ->assertOk()
            ->assertJson(['redirect' => cp_route('two-factor-setup')]);

        $user->fresh();

        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_secret);

        Event::assertDispatched(TwoFactorAuthenticationDisabled::class, fn ($event) => $event->user->id === $user->id);
    }

    #[Test]
    public function it_disables_two_factor_authentication_for_another_user()
    {
        Event::fake();

        $otherUser = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->withActiveElevatedSession()
            ->delete(cp_route('users.two-factor.disable', [
                'user' => $otherUser->id,
            ]))
            ->assertOk()
            ->assertJson(['redirect' => null]);

        $otherUser->fresh();

        $this->assertNull($otherUser->two_factor_confirmed_at);
        $this->assertNull($otherUser->two_factor_recovery_codes);
        $this->assertNull($otherUser->two_factor_secret);

        Event::assertDispatched(TwoFactorAuthenticationDisabled::class, fn ($event) => $event->user->id === $otherUser->id);
    }

    #[Test]
    public function it_disables_two_factor_authentication_for_another_user_when_two_factor_is_enforced()
    {
        Event::fake();

        // Enforced for everyone
        config()->set('statamic.users.two_factor_enforced_roles', ['*']);

        $otherUser = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->withActiveElevatedSession()
            ->delete(cp_route('users.two-factor.disable', [
                'user' => $otherUser->id,
            ]))
            ->assertOk()
            ->assertJson(['redirect' => null]);

        $otherUser->fresh();

        $this->assertNull($otherUser->two_factor_confirmed_at);
        $this->assertNull($otherUser->two_factor_recovery_codes);
        $this->assertNull($otherUser->two_factor_secret);

        Event::assertDispatched(TwoFactorAuthenticationDisabled::class, fn ($event) => $event->user->id === $otherUser->id);
    }

    #[Test]
    public function it_cant_disable_two_factor_authentication_without_elevated_session()
    {
        Event::fake();

        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->delete(cp_route('users.two-factor.disable', [
                'user' => $user->id,
            ]))
            ->assertRedirect('/cp/auth/confirm-password');

        $user->fresh();

        $this->assertNotNull($user->two_factor_confirmed_at);
        $this->assertNotNull($user->two_factor_recovery_codes);
        $this->assertNotNull($user->two_factor_secret);

        Event::assertNotDispatched(TwoFactorAuthenticationDisabled::class, fn ($event) => $event->user->id === $user->id);
    }

    private function user()
    {
        return tap(User::make()->makeSuper()->email('david@hasselhoff.com'))->save();
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

    private function withActiveElevatedSession()
    {
        return $this->session(['statamic_elevated_session' => now()->timestamp]);
    }
}
