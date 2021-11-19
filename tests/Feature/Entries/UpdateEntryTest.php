<?php

namespace Tests\Feature\Entries;

use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateEntryTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function published_entry_gets_saved_to_working_copy()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function draft_entry_gets_saved_to_content()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function validation_error_returns_back()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function user_without_permission_to_manage_publish_state_cannot_change_publish_status()
    {
        $this->markTestIncomplete();
    }

    private function save($entry, $payload)
    {
        return $this->patch($entry->updateUrl(), $payload);
    }
}
