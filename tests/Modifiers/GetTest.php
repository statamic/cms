<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class GetTest extends TestCase
{
    private function modify($value, $key)
    {
        return Modify::value($value)->get([$key])->fetch();
    }

    #[Test]
    public function it_gets_a_field_value()
    {
        $item = new GetTestItem;

        $this->assertEquals('Hello', $this->modify($item, 'title'));
    }

    #[Test]
    public function it_returns_falsy_field_values_instead_of_the_original_value()
    {
        $item = new GetTestItem;

        $this->assertSame('', $this->modify($item, 'empty'));
        $this->assertSame(0, $this->modify($item, 'zero'));
        $this->assertSame(false, $this->modify($item, 'false'));
    }

    #[Test]
    public function it_does_not_dispatch_to_methods()
    {
        $item = new GetTestItem;

        $this->assertSame($item, $this->modify($item, 'url'));
        $this->assertSame($item, $this->modify($item, 'delete'));

        $this->assertFalse($item->deleted);
    }
}

class GetTestItem
{
    public $deleted = false;

    public function toArray()
    {
        return [
            'title' => 'Hello',
            'empty' => '',
            'zero' => 0,
            'false' => false,
        ];
    }

    public function url()
    {
        return 'https://example.com';
    }

    public function delete()
    {
        $this->deleted = true;
    }
}
