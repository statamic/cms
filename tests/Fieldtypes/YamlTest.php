<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Yaml;
use Tests\TestCase;

class YamlTest extends TestCase
{
    #[Test]
    #[DataProvider('processProvider')]
    public function it_processes($value, $expected)
    {
        $this->assertSame($expected, $this->fieldtype()->process($value));
    }

    public static function processProvider()
    {
        return [
            'string' => ['alfa', 'alfa'],
            'multiline string' => ["alfa\nbravo", "alfa\nbravo"],
            'empty' => ['', null],
            'associative array' => ["alfa: bravo\ncharlie: delta", ['alfa' => 'bravo', 'charlie' => 'delta']],
            'single item associative array' => ['alfa: bravo', ['alfa' => 'bravo']],
            'numeric array' => ["- alfa\n- bravo", ['alfa', 'bravo']],
            'single item numeric array' => ['- alfa', ['alfa']],

            // If we were checking for "- " to determine if it's yaml, we'd get these wrong.
            'string with dash space' => ['alfa - bravo', 'alfa - bravo'],
            'multiline string with dash space' => ["alfa\n- bravo", "alfa\n- bravo"],
        ];
    }

    private function fieldtype()
    {
        return (new Yaml)->setField(new Field('test', ['type' => 'yaml']));
    }
}
