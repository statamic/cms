<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class AntlersTest extends TestCase
{
    #[Test]
    public function it_parses_as_antlers(): void
    {
        $modified = $this->modify('foo {{ foo }} bar {{ bar }}', ['foo' => 'alfa', 'bar' => 'bravo']);
        $this->assertEquals('foo alfa bar bravo', $modified);
    }

    private function modify($value, array $context = [])
    {
        return Modify::value($value)->context($context)->antlers()->fetch();
    }
}
