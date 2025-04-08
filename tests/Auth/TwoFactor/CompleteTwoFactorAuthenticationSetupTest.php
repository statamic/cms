<?php

namespace Tests\Auth\TwoFactor;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\CompleteTwoFactorAuthenticationSetup;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CompleteTwoFactorAuthenticationSetupTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(CompleteTwoFactorAuthenticationSetup::class);
    }

    #[Test]
    public function it_correctly_updates_the_user_as_having_two_factor_enabled()
    {
        $this->freezeTime();

        $user = tap(User::make()->makeSuper())->save();

        $this->assertNull($user->two_factor_completed);

        $this->action->__invoke($user);

        $this->assertNotNull($user->two_factor_completed);
        $this->assertEquals(now(), $user->two_factor_completed);
    }
}
