<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

#[Group('array')]
class FilterEmptyTest extends TestCase
{
    #[Test]
    public function it_removes_null_values_from_an_array(): void
    {
        $modified = $this->modify(['one' => 'one', null, 'two' => 'two', 'three' => null, 'four' => 'four']);
        $this->assertEquals(['one' => 'one', 'two' => 'two', 'four' => 'four'], $modified);
    }

    #[Test]
    public function it_reindexes_numerical_arrays(): void
    {
        $modified = $this->modify(['one', null, 'two', null, 'four']);
        $this->assertEquals([0 => 'one', 1 => 'two', 2 => 'four'], $modified);
    }

    #[Test]
    public function it_removes_null_values_from_a_collection(): void
    {
        $modified = $this->modify(collect(['one' => 'one', null, 'two' => 'two', 'three' => null, 'four' => 'four']));
        $this->assertEquals(['one' => 'one', 'two' => 'two', 'four' => 'four'], $modified->all());
    }

    #[Test]
    public function it_reindexes_numerical_collections(): void
    {
        $modified = $this->modify(collect(['one', null, 'two', null, 'four']));
        $this->assertEquals([0 => 'one', 1 => 'two', 2 => 'four'], $modified->all());
    }

    private function modify($value, $params = [])
    {
        return Modify::value($value)->filterEmpty($params)->fetch();
    }
}
