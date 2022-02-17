<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class ShrugTest extends TestCase
{
    /** @test */
    public function it_shruggs(): void
    {
        $modified = $this->modify('');
        $this->assertEquals('¯\_(ツ)_/¯', $modified);
    }

    private function modify(string $value)
    {
        return Modify::value($value)->shrug()->fetch();
    }
}
