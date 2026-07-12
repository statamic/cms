<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class LowerTest extends TestCase
{
    #[Test]
    public function it_converts_all_chars_to_lowercase(): void
    {
        $modified = $this->modify('I DON\'T KNOW WHAT WE\'RE YELLING ABOUT');
        $this->assertEquals('i don\'t know what we\'re yelling about', $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->lower()->fetch();
    }
}
