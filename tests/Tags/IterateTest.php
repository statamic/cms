<?php

namespace Tests\Tags;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Statamic\Fields\Value;
use Statamic\Fields\Values;
use Tests\TestCase;

class IterateTest extends TestCase
{
    #[Test]
    #[DataProvider('iterateProvider')]
    public function it_iterates($value)
    {
        $template = '{{ foreach:fieldname }}<{{ key }},{{ value }}>{{ /foreach:fieldname }}';

        $this->assertSame('<alfa,one><bravo,two>', $this->tag($template, ['fieldname' => $value]));
    }

    public static function iterateProvider()
    {
        return [
            'array' => [
                ['alfa' => 'one', 'bravo' => 'two'],
            ],
            'collection' => [
                collect(['alfa' => 'one', 'bravo' => 'two']),
            ],
            'values' => [
                new Values(['alfa' => 'one', 'bravo' => 'two']),
            ],
            'value with array' => [
                new Value(['alfa' => 'one', 'bravo' => 'two']),
            ],
            'value with collection' => [
                new Value(collect(['alfa' => 'one', 'bravo' => 'two'])),
            ],
            'value with values' => [
                new Value(new Values(['alfa' => 'one', 'bravo' => 'two'])),
            ],
        ];
    }

    private function tag($tag, $context = [])
    {
        return (string) Parse::template($tag, $context);
    }
}
