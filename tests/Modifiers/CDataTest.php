<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class CDataTest extends TestCase
{
    /** @test */
    public function it_wraps_the_string_in_cdata_tags(): void
    {
        $modified = $this->modify('My Very Own Podcast');
        $this->assertEquals('<![CDATA[My Very Own Podcast]]>', $modified);
    }

    private function modify(string $value)
    {
        return Modify::value($value)->cdata()->fetch();
    }
}
