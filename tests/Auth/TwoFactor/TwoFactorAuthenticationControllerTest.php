<?php

namespace Tests\Auth\TwoFactor;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ViewErrorBag;
use PHPUnit\Framework\Attributes\Test;
use PragmaRX\Google2FA\Google2FA;
use Statamic\Auth\TwoFactor\TwoFactorAuthenticationProvider;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Events\TwoFactorAuthenticationDisabled;
use Statamic\Events\TwoFactorAuthenticationEnabled;
use Statamic\Facades\User;
use Statamic\Http\Middleware\CP\EnforceTwoFactor;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class TwoFactorAuthenticationControllerTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
    public function it_enables_two_factor_authentication()
    {
        Event::fake();

        $user = $this->user();

        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);

        $this
            ->actingAs($user)
            ->get(cp_route('users.two-factor.enable', $user->id))
            ->assertOk()
            ->assertJsonStructure(['qr', 'secret_key', 'confirm_url']);

        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_recovery_codes);

        Event::assertDispatched(TwoFactorAuthenticationEnabled::class, fn ($event) => $event->user->id === $user->id);
    }

    #[Test]
    public function it_cant_enable_two_factor_authentication_for_another_user()
    {
        Event::fake();

        $user = $this->user();

        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);

        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->get(cp_route('users.two-factor.enable', $user->id))
            ->assertForbidden();

        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);

        Event::assertNotDispatched(TwoFactorAuthenticationEnabled::class, fn ($event) => $event->user->id === $user->id);
    }

    #[Test]
    public function it_doesnt_regenerate_secret_when_validation_error_is_present()
    {
        Event::fake();

        $user = $this->user();
        $user->set('two_factor_secret', $originalSecret = encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()));
        $user->set('two_factor_recovery_codes', $originalRecoveryCodes = encrypt(['abc', 'def', 'ghi', 'jkl', 'mno', 'pqr', 'stu', 'vwx']));
        $user->save();

        $this
            ->actingAs($user)
            ->session([
                'errors' => collect(['code' => 'The provided two factor authentication code was invalid.']),
            ])
            ->get(cp_route('users.two-factor.enable', $user->id))
            ->assertOk()
            ->assertJsonStructure(['qr', 'secret_key', 'confirm_url']);

        $this->assertEquals($originalSecret, $user->two_factor_secret);
        $this->assertEquals($originalRecoveryCodes, $user->two_factor_recovery_codes);

        Event::assertNotDispatched(TwoFactorAuthenticationEnabled::class, fn ($event) => $event->user->id === $user->id);
    }

    #[Test]
    public function it_confirms_two_factor_authentication()
    {
        $user = $this->user();
        $user->set('two_factor_secret', encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()));
        $user->set('two_factor_recovery_codes', encrypt(['abc', 'def', 'ghi', 'jkl', 'mno', 'pqr', 'stu', 'vwx']));
        $user->save();

        $this->assertNull($user->two_factor_confirmed_at);

        $this
            ->actingAs($user)
            ->post(cp_route('users.two-factor.confirm', $user->id), [
                'code' => $this->getOneTimeCode($user),
            ])
            ->assertOk()
            ->assertJson([
                'complete_url' => cp_route('users.two-factor.complete', $user->id),
            ]);

        $this->assertNotNull($user->two_factor_confirmed_at);
    }

    #[Test]
    public function it_cant_confirm_two_factor_authentication_without_valid_code()
    {
        $user = $this->user();
        $user->set('two_factor_secret', encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()));
        $user->set('two_factor_recovery_codes', encrypt(['abc', 'def', 'ghi', 'jkl', 'mno', 'pqr', 'stu', 'vwx']));
        $user->save();

        $this->assertNull($user->two_factor_confirmed_at);

        $this
            ->actingAs($user)
            ->post(cp_route('users.two-factor.confirm', $user->id), [
                'code' => '123456',
            ])
            ->assertSessionHasErrors('code');

        $this->assertNull($user->two_factor_confirmed_at);
    }

    #[Test]
    public function it_cant_confirm_two_factor_authentication_for_another_user()
    {
        $user = $this->user();
        $user->set('two_factor_secret', encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()));
        $user->set('two_factor_recovery_codes', encrypt(['abc', 'def', 'ghi', 'jkl', 'mno', 'pqr', 'stu', 'vwx']));
        $user->save();

        $this->assertNull($user->two_factor_confirmed_at);

        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->post(cp_route('users.two-factor.confirm', $user->id), [
                'code' => $this->getOneTimeCode($user),
            ])
            ->assertForbidden();

        $this->assertNull($user->two_factor_confirmed_at);
    }

    #[Test]
    public function it_completes_two_factor_authentication()
    {
        $user = $this->user();
        $user->set('two_factor_secret', encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()));
        $user->set('two_factor_recovery_codes', encrypt(['abc', 'def', 'ghi', 'jkl', 'mno', 'pqr', 'stu', 'vwx']));
        $user->save();

        $this->assertNull($user->two_factor_completed);

        $this
            ->actingAs($user)
            ->post(cp_route('users.two-factor.complete', $user->id))
            ->assertOk();

        $this->assertNotNull($user->two_factor_completed);
    }

    #[Test]
    public function it_cant_complete_two_factor_authentication_for_another_user()
    {
        $user = $this->user();
        $user->set('two_factor_secret', encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()));
        $user->set('two_factor_recovery_codes', encrypt(['abc', 'def', 'ghi', 'jkl', 'mno', 'pqr', 'stu', 'vwx']));
        $user->save();

        $this->assertNull($user->two_factor_completed);

        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->post(cp_route('users.two-factor.complete', $user->id))
            ->assertForbidden();

        $this->assertNull($user->two_factor_completed);
    }

    #[Test]
    public function it_disables_two_factor_authentication_for_the_current_user()
    {
        Event::fake();

        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->delete(cp_route('users.two-factor.disable', [
                'user' => $user->id,
            ]))
            ->assertOk()
            ->assertJson(['redirect' => null]);

        $user->fresh();

        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertNull($user->two_factor_completed);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_secret);

        Event::assertDispatched(TwoFactorAuthenticationDisabled::class, fn ($event) => $event->user->id === $user->id);
    }

    #[Test]
    public function it_disables_two_factor_authentication_for_the_current_user_when_two_factor_is_enforced()
    {
        Event::fake();

        // Enforced for everyone
        config()->set('statamic.users.two_factor.enforced_roles', ['*']);

        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->delete(cp_route('users.two-factor.disable', [
                'user' => $user->id,
            ]))
            ->assertOk()
            ->assertJson(['redirect' => cp_route('two-factor-setup')]);

        $user->fresh();

        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertNull($user->two_factor_completed);
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
            ->delete(cp_route('users.two-factor.disable', [
                'user' => $otherUser->id,
            ]))
            ->assertOk()
            ->assertJson(['redirect' => null]);

        $otherUser->fresh();

        $this->assertNull($otherUser->two_factor_confirmed_at);
        $this->assertNull($otherUser->two_factor_completed);
        $this->assertNull($otherUser->two_factor_recovery_codes);
        $this->assertNull($otherUser->two_factor_secret);

        Event::assertDispatched(TwoFactorAuthenticationDisabled::class, fn ($event) => $event->user->id === $otherUser->id);
    }

    #[Test]
    public function it_disables_two_factor_authentication_for_another_user_when_two_factor_is_enforced()
    {
        Event::fake();

        // Enforced for everyone
        config()->set('statamic.users.two_factor.enforced_roles', ['*']);

        $otherUser = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($this->userWithTwoFactorEnabled())
            ->delete(cp_route('users.two-factor.disable', [
                'user' => $otherUser->id,
            ]))
            ->assertOk()
            ->assertJson(['redirect' => null]);

        $otherUser->fresh();

        $this->assertNull($otherUser->two_factor_confirmed_at);
        $this->assertNull($otherUser->two_factor_completed);
        $this->assertNull($otherUser->two_factor_recovery_codes);
        $this->assertNull($otherUser->two_factor_secret);

        Event::assertDispatched(TwoFactorAuthenticationDisabled::class, fn ($event) => $event->user->id === $otherUser->id);
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
            'two_factor_completed' => now()->timestamp,
            'two_factor_secret' => encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()),
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ]);

        $user->save();

        return $user;
    }

    private function getOneTimeCode($user): string
    {
        $internalProvider = app(Google2FA::class);

        return $internalProvider->getCurrentOtp($user->twoFactorSecretKey());
    }
}
