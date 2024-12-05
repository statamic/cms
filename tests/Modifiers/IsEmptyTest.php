<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

#[Group('array')]
class IsEmptyTest extends TestCase
{
    #[Test]
    public function it_checks_if_its_empty()
    {
        $this->assertTrue($this->modify('')); // empty string is empty
        $this->assertTrue($this->modify([])); // empty array is empty

        $this->assertFalse($this->modify(['foo' => 'bar'])); // definitely not empty

        $this->assertTrue($this->modify(['foo' => ''])); // just consists of empty strings
        $this->assertTrue($this->modify(['foo' => '', 'bar' => '']));

        $this->assertFalse($this->modify(null)); // nulls are not empty
        $this->assertFalse($this->modify(['foo' => null])); // array of nulls are not empty
        $this->assertFalse($this->modify(['foo' => '', 'bar' => null]));

        $this->assertTrue($this->modify(['foo' => []])); // recursion
        $this->assertTrue($this->modify(['foo' => ['bar' => []]]));
        $this->assertTrue($this->modify(['foo' => ['bar' => ['baz' => '']]]));
        $this->assertFalse($this->modify(['foo' => ['bar' => ['baz' => 'qux']]]));
        $this->assertFalse($this->modify(['foo' => ['bar' => ['baz' => null]]]));
    }

    public function modify($arr)
    {
        return Modify::value($arr)->isEmpty()->fetch();
    }
}
