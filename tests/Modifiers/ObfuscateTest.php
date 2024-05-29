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

    /** @test */
    public function it_obfuscates_a_multibyte_string(): void
    {
        $modified = $this->modify('é');
        $this->assertTrue(in_array($modified, ['&#233;', '&#xE9;', 'é']));

        $modified = $this->modify('ß');
        $this->assertTrue(in_array($modified, ['&#223;', '&#xDF;', 'ß']));
    }

    private function modify($value)
    {
        return Modify::value($value)->obfuscate()->fetch();
    }
}
