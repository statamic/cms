<?php

namespace Tests\Feature\Entries;

use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EditEntryTest extends TestCase
{
    use FakesRoles;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_shows_the_entry_form()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_overrides_values_from_the_working_copy()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_marks_as_read_only_if_you_only_have_view_permission()
    {
        $this->markTestIncomplete();
    }
}
