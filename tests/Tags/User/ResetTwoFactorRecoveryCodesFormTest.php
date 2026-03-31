<?php

namespace Tests\Tags\User;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Statamic\Contracts\Auth\TwoFactor\TwoFactorAuthenticationProvider;
use Statamic\Facades\Parse;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('2fa')]
class ResetTwoFactorRecoveryCodesFormTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function tag($tag)
    {
        return Parse::template($tag, trusted: true);
    }

    #[Test]
    public function it_renders_for_user_with_2fa()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this->actingAs($user);

        $output = $this->tag('{{ user:reset_two_factor_recovery_codes_form }}{{ /user:reset_two_factor_recovery_codes_form }}');

        $this->assertStringStartsWith('<form method="POST" action="http://localhost/!/auth/two-factor/recovery-codes">', $output);
        $this->assertStringContainsString(csrf_field(), $output);
    }

    #[Test]
    public function it_does_not_render_for_user_without_2fa()
    {
        $user = $this->user();

        $this->actingAs($user);

        $output = $this->tag('{{ user:reset_two_factor_recovery_codes_form }}<p>inside</p>{{ /user:reset_two_factor_recovery_codes_form }}');

        $this->assertStringNotContainsString('<form', $output);
        $this->assertStringNotContainsString('<p>inside</p>', $output);
    }

    #[Test]
    public function it_does_not_render_for_guests()
    {
        $output = $this->tag('{{ user:reset_two_factor_recovery_codes_form }}<p>inside</p>{{ /user:reset_two_factor_recovery_codes_form }}');

        $this->assertStringNotContainsString('<form', $output);
        $this->assertStringNotContainsString('<p>inside</p>', $output);
    }

    #[Test]
    public function it_renders_with_redirect_param()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this->actingAs($user);

        $output = $this->tag('{{ user:reset_two_factor_recovery_codes_form redirect="/recovery-codes" }}{{ /user:reset_two_factor_recovery_codes_form }}');

        $this->assertStringContainsString('<input type="hidden" name="_redirect" value="/recovery-codes" />', $output);
    }

    #[Test]
    public function it_regenerates_codes_and_redirects()
    {
        $user = $this->userWithTwoFactorEnabled();
        $originalCodes = $user->twoFactorRecoveryCodes();

        $this
            ->actingAs($user)
            ->post(route('statamic.users.two-factor.recovery-codes.generate'), [
                '_redirect' => '/recovery-codes',
            ])
            ->assertRedirect('/recovery-codes')
            ->assertSessionHas('success');

        $newCodes = $user->fresh()->twoFactorRecoveryCodes();
        $this->assertNotEquals($originalCodes, $newCodes);
        $this->assertCount(8, $newCodes);
    }

    #[Test]
    public function it_redirects_back_without_redirect_param()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this
            ->actingAs($user)
            ->from('/account/security')
            ->post(route('statamic.users.two-factor.recovery-codes.generate'), [
                '_redirect' => '/account/security',
            ])
            ->assertRedirect('/account/security')
            ->assertSessionHas('success');
    }

    private function user()
    {
        return tap(User::make()->makeSuper()->email('test@example.com'))->save();
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
