<?php

namespace Tests\Feature\Entries;

use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StoreEntryTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function entry_gets_created()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function validation_error_returns_back()
    {
        $this->markTestIncomplete();
    }
}
