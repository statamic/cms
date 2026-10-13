<?php

namespace Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\ConfigFields;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Integer;
use Tests\TestCase;

class IntegerTest extends TestCase
{
    #[Test]
    #[DataProvider('configValueProvider')]
    public function it_pre_processes_config_values($value, $expected)
    {
        $fields = (new ConfigFields([
            ['handle' => 'max', 'field' => ['type' => 'integer']],
        ]))->addValues(['max' => $value]);

        $this->assertSame($expected, $fields->preProcess()->values()->get('max'));
    }

    public static function configValueProvider()
    {
        return [
            'integer' => [5, 5],
            'numeric string' => ['5', 5],
            'zero' => [0, 0],
            'null' => [null, null],
        ];
    }

    #[Test]
    #[DataProvider('rulesProvider')]
    public function it_adds_min_and_max_rules($config, $expected)
    {
        $field = (new Integer)->setField(new Field('test', array_merge([
            'type' => 'integer',
        ], $config)));

        $this->assertSame($expected, $field->rules());
    }

    public static function rulesProvider()
    {
        return [
            'min and max' => [['min' => 1, 'max' => 10], ['integer', 'min:1', 'max:10']],
            'zeroes' => [['min' => 0, 'max' => 0], ['integer', 'min:0', 'max:0']],
            'nulls' => [['min' => null, 'max' => null], ['integer']],
            'not configured' => [[], ['integer']],
        ];
    }
}
