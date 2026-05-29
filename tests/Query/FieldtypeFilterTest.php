<?php

namespace Tests\Query;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Text;
use Tests\TestCase;

class FieldtypeFilterTest extends TestCase
{
    #[Test]
    #[DataProvider('completenessProvider')]
    public function it_determines_if_a_filter_is_complete($values, $expected)
    {
        $filter = (new Text)->setField(new Field('test', ['type' => 'text']))->filter();

        $this->assertEquals($expected, $filter->isComplete($values));
    }

    public static function completenessProvider()
    {
        return [
            'no operator' => [['value' => 'foo'], false],
            'operator but no value' => [['operator' => '='], false],
            'operator and value' => [['operator' => '=', 'value' => 'foo'], true],
            'zero string value' => [['operator' => '=', 'value' => '0'], true],
            'zero integer value' => [['operator' => '=', 'value' => 0], true],
            'null value' => [['operator' => '=', 'value' => null], false],
            'empty string value' => [['operator' => '=', 'value' => ''], false],
            'null operator without value' => [['operator' => 'null'], true],
            'not-null operator without value' => [['operator' => 'not-null'], true],
        ];
    }
}
