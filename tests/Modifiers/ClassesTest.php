<?php

namespace Tests\Modifiers;

use Statamic\Modifiers\Modify;
use Tests\TestCase;

/**
 * @group array
 */
class ClassesTest extends TestCase
{
    /** @test */
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
