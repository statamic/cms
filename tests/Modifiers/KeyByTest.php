<?php

namespace Tests\Modifiers;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class KeyByTest extends TestCase
{
    #[Test]
    public function it_rekeys_an_array(): void
    {
        $modified = $this->modify([
            ['handle' => 'one', 'title' => 'One'],
            ['handle' => 'two', 'title' => 'Two'],
        ], 'handle');

        $this->assertEquals([
            'one' => ['handle' => 'one', 'title' => 'One'],
            'two' => ['handle' => 'two', 'title' => 'Two'],
        ], $modified);
    }

    #[Test]
    public function it_rekeys_a_collection(): void
    {
        $modified = $this->modify(collect([
            ['handle' => 'one', 'title' => 'One'],
            ['handle' => 'two', 'title' => 'Two'],
        ]), 'handle');

        $this->assertInstanceOf(Collection::class, $modified);
        $this->assertEquals([
            'one' => ['handle' => 'one', 'title' => 'One'],
            'two' => ['handle' => 'two', 'title' => 'Two'],
        ], $modified->all());
    }

    private function modify($value, $key)
    {
        return Modify::value($value)->keyBy($key)->fetch();
    }
}
