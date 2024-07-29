<?php

namespace Tests\Auth\Protect;

use PHPUnit\Framework\Attributes\Test;

class NoProtectionTest extends PageProtectionTestCase
{
    #[Test]
    public function no_protect_variable_means_no_protection_occurs()
    {
        $this
            ->requestPageProtectedBy(null)
            ->assertOk();
    }
}
