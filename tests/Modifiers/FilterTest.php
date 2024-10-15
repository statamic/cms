<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

#[Group('array')]
class FilterTest extends TestCase
{
    #[Test]
    public function it_removes_null_values_from_an_array_and_keeps_keys(): void
    {
        $modified = $this->modify(['one' => 'one', null, 'two' => 'two', 'three' => 'three']);
        $this->assertEquals(['one' => 'one', 'two' => 'two', 'three' => 'three'], $modified);

        $modified = $this->modify(['one' => 'one', null, 'two' => 'two', 'three' => 'three'], [true]);
        $this->assertSame(['one' => 'one', 'two' => 'two', 'three' => 'three'], $modified);
    }

    #[Test]
    public function it_removes_null_values_from_an_array_and_discards_keys(): void
    {
        $modified = $this->modify(['one' => 'one', null, 'two' => 'two', 'three' => 'three'], [false]);
        $this->assertEquals(['one', 'two', 'three'], $modified);
    }

    private function modify($value, $params = [])
    {
        return Modify::value($value)->filter($params)->fetch();
    }
}
