<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class ToBoolTest extends TestCase
{
    #[Test]
    public function it_bools(): void
    {
        $this->assertTrue($this->modify(1));
        $this->assertFalse($this->modify(0));
        $this->assertTrue($this->modify('foo'));
        $this->assertFalse($this->modify('false'));
        $this->assertFalse($this->modify('FALSE'));
        $this->assertFalse($this->modify('False'));
        $this->assertTrue($this->modify(new \stdClass));
    }

    private function modify($value)
    {
        return Modify::value($value)->toBool()->fetch();
    }
}
