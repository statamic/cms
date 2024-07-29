<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

#[Group('array')]
class ClassesTest extends TestCase
{
    #[Test]
    public function it_conditionally_applies_class_names(): void
    {
        $this->assertSame('one two', $this->modify(['one' => true, 'two' => true])->fetch());
        $this->assertSame('one', $this->modify(['one' => true, 'two' => false])->fetch());
    }

    private function modify($value)
    {
        return Modify::value($value)->classes();
    }
}
