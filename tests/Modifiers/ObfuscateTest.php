<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class ObfuscateTest extends TestCase
{
    /** @test */
    public function it_obfuscates_a_string(): void
    {
        $modified = $this->modify('A');
        $this->assertTrue(in_array($modified, ['&#65;', '&#x41;', 'A']));
    }

    private function modify($value)
    {
        return Modify::value($value)->obfuscate()->fetch();
    }
}
