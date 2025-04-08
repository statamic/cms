<?php

namespace Tests\Auth\TwoFactor;

use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\CreateRecoveryCodes;
use Statamic\Facades\User;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class CreateRecoveryCodesTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(CreateRecoveryCodes::class);
    }

    #[Test]
    public function it_correctly_creates_eight_recovery_codes_for_a_user()
    {
        $user = tap(User::make()->makeSuper())->save();

        $this->assertNull($user->two_factor_recovery_codes);

        $this->action->__invoke($user);

        $this->assertIsString($user->two_factor_recovery_codes);

        $recoveryCodes = decrypt($user->two_factor_recovery_codes);
        $this->assertTrue(Str::isJson($recoveryCodes));

        $decryptedRecoveryCodes = json_decode($recoveryCodes, true);
        $this->assertIsArray($decryptedRecoveryCodes);
        $this->assertCount(8, $decryptedRecoveryCodes);
    }
}
