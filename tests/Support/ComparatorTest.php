<?php

namespace Tests\Support;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Compare;
use Tests\TestCase;

class ComparatorTest extends TestCase
{
    protected function assertFirst($value)
    {
        $this->assertEquals(1, $value);
    }

    protected function assertSecond($value)
    {
        $this->assertEquals(-1, $value);
    }

    protected function assertEqual($value)
    {
        $this->assertEquals(0, $value);
    }

    #[Test]
    public function it_compares_strings_case_insensitively()
    {
        $this->assertSecond(Compare::strings('a', 'b'));
        $this->assertFirst(Compare::strings('b', 'a'));
        $this->assertEqual(Compare::strings('a', 'a'));

        $this->assertEqual(Compare::strings('a', 'A'));
        $this->assertEqual(Compare::strings('A', 'a'));
        $this->assertSecond(Compare::strings('A', 'b'));
        $this->assertSecond(Compare::strings('a', 'B'));
        $this->assertFirst(Compare::strings('B', 'a'));
        $this->assertFirst(Compare::strings('b', 'A'));
    }

    #[Test]
    public function it_compares_numbers()
    {
        $this->assertSecond(Compare::numbers(1, 2));
        $this->assertFirst(Compare::numbers(2, 1));
        $this->assertEqual(Compare::numbers(1, 1));
    }

    #[Test]
    public function it_compares_values()
    {
        $this->assertSecond(Compare::values('a', 'b'));
        $this->assertFirst(Compare::values('b', 'a'));
        $this->assertEqual(Compare::values('a', 'a'));

        $this->assertSecond(Compare::values(1, 2));
        $this->assertFirst(Compare::values(2, 1));
        $this->assertEqual(Compare::values(1, 1));
    }
}
