<?php

namespace Tests\Modifiers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\EntryCollection;
use Statamic\Modifiers\Modify;
use Tests\TestCase;

class IsIterableTest extends TestCase
{
    public static function iterablesProvider(): array
    {
        return [
            'array' => [true, ['foo', 'bar', 'baz']],
            'collection' => [true, collect(['foo', 'bar', 'baz'])],
            'entries_collection' => [true, new EntryCollection()],
            'no_iterable' => [false, 'string'],
        ];
    }

    #[Test]
    #[DataProvider('iterablesProvider')]
    public function it_returns_true_if_input_is_iterable($expected, $input): void
    {
        $modified = $this->modify($input);
        $this->assertEquals($expected, $modified);
    }

    private function modify($value)
    {
        return Modify::value($value)->isIterable()->fetch();
    }
}
