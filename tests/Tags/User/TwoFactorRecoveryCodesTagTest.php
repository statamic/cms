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
class TwoFactorRecoveryCodesTagTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function tag($tag)
    {
        return Parse::template($tag, trusted: true);
    }

    #[Test]
    public function it_renders_codes_for_user_with_2fa()
    {
        $user = $this->userWithTwoFactorEnabled();

        $this->actingAs($user);

        $output = $this->tag('{{ user:two_factor_recovery_codes }}<li>{{ code }}</li>{{ /user:two_factor_recovery_codes }}');

        $codes = $user->twoFactorRecoveryCodes();

        foreach ($codes as $code) {
            $this->assertStringContainsString("<li>{$code}</li>", $output);
        }
    }

    #[Test]
    public function it_does_not_render_for_user_without_2fa()
    {
        $user = $this->user();

        $this->actingAs($user);

        $output = $this->tag('{{ user:two_factor_recovery_codes }}<li>{{ code }}</li>{{ /user:two_factor_recovery_codes }}');

        $this->assertStringNotContainsString('<li>', $output);
    }

    #[Test]
    public function it_does_not_render_for_guests()
    {
        $output = $this->tag('{{ user:two_factor_recovery_codes }}<li>{{ code }}</li>{{ /user:two_factor_recovery_codes }}');

        $this->assertStringNotContainsString('<li>', $output);
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
