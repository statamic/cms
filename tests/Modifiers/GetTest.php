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
    public function it_returns_falsy_field_values_instead_of_falling_through_to_a_method()
    {
        $item = new GetTestItem;

        $this->assertSame('', $this->modify($item, 'empty'));
        $this->assertSame(0, $this->modify($item, 'zero'));
        $this->assertSame(false, $this->modify($item, 'false'));
    }

    #[Test]
    public function it_dispatches_to_a_non_destructive_accessor_method()
    {
        $item = new GetTestItem;

        $this->assertEquals('https://example.com', $this->modify($item, 'url'));
    }

    #[Test]
    public function it_does_not_dispatch_to_destructive_methods()
    {
        $item = new GetTestItem;

        $result = $this->modify($item, 'delete');

        $this->assertFalse($item->deleted);
        $this->assertSame($item, $result);
    }

    #[Test]
    public function it_does_not_dispatch_to_destructive_methods_case_insensitively()
    {
        // Str::slug() lowercases the parameter (e.g. "deleteQuietly" => "deletequietly"),
        // but method_exists() is case-insensitive, so the denylist must match regardless of case.
        $item = new GetTestItem;

        $this->modify($item, 'deletequietly');
        $this->assertFalse($item->deletedQuietly);

        $this->modify($item, 'savequietly');
        $this->assertFalse($item->savedQuietly);
    }
}

class GetTestItem
{
    public $deleted = false;
    public $deletedQuietly = false;
    public $saved = false;
    public $savedQuietly = false;

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

    public function deleteQuietly()
    {
        $this->deletedQuietly = true;
    }

    public function save()
    {
        $this->saved = true;
    }

    public function saveQuietly()
    {
        $this->savedQuietly = true;
    }
}
