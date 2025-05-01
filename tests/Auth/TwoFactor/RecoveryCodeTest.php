<?php

namespace Tests\Auth\TwoFactor;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Auth\TwoFactor\RecoveryCode;
use Tests\TestCase;

#[Group('2fa')]
class RecoveryCodeTest extends TestCase
{
    #[Test]
    public function it_generates_a_code_made_up_of_ten_characters_a_dash_and_another_ten_characters()
    {
        $code = RecoveryCode::generate();

        $this->assertTrue((bool) preg_match('/[a-zA-Z0-9]{10}-[a-zA-Z0-9]{10}/', $code));
    }

    #[Test]
    public function it_generates_a_different_code_on_each_call()
    {
        $code1 = RecoveryCode::generate();

        $this->assertNotNull($code1);

        $code2 = RecoveryCode::generate();

        $this->assertNotNull($code2);
        $this->assertNotEquals($code1, $code2);

        $code3 = RecoveryCode::generate();

        $this->assertNotNull($code3);
        $this->assertNotEquals($code1, $code3);
        $this->assertNotEquals($code2, $code3);
    }
}
