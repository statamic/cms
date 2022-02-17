<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

class IsBlankTest extends TestCase
{
    /** @test */
    public function it_returns_true_if_input_is_blank(): void
    {
        $modified = $this->modify('');
        $this->assertTrue($modified);
    }

    /** @test */
    public function it_returns_false_if_input_is_not_blank(): void
    {
        $modified = $this->modify('BRAINSSSS');
        $this->assertFalse($modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->isBlank()->fetch();
    }
}
