<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class LcfirstTest extends TestCase
{
    #[Test]
    public function it_converts_first_char_of_string_to_lowercase_char(): void
    {
        $modified = $this->modify('WOW');
        $this->assertEquals('wOW', $modified);

        $modified = $this->modify('Wow');
        $this->assertEquals('wow', $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->lcfirst()->fetch();
    }
}
