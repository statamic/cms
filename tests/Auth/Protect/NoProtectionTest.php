<?php

namespace Tests\Auth\Protect;

class NoProtectionTest extends PageProtectionTestCase
{
    /** @test */
    public function no_protect_variable_means_no_protection_occurs()
    {
        $this
            ->requestPageProtectedBy(null)
            ->assertOk();
    }
}
