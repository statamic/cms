<?php

namespace Tests\Query;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Integer as IntegerFieldtype;
use Statamic\Fieldtypes\Text;
use Statamic\Query\Scopes\Filters\Fields\Dimensions;
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

    #[Test]
    #[DataProvider('dimensionsCompletenessProvider')]
    public function it_determines_if_a_dimensions_filter_is_complete($values, $expected)
    {
        $fieldtype = (new IntegerFieldtype)->setField(new Field('test', ['type' => 'integer']));
        $filter = new Dimensions($fieldtype);

        $this->assertEquals($expected, $filter->isComplete($values));
    }

    public static function dimensionsCompletenessProvider()
    {
        return [
            'all fields present' => [['dimension' => 'width', 'operator' => '=', 'value' => '100'], true],
            'zero value' => [['dimension' => 'width', 'operator' => '=', 'value' => 0], true],
            'zero string value' => [['dimension' => 'width', 'operator' => '=', 'value' => '0'], true],
            'null value' => [['dimension' => 'width', 'operator' => '=', 'value' => null], false],
            'missing value' => [['dimension' => 'width', 'operator' => '='], false],
            'missing dimension' => [['operator' => '=', 'value' => '100'], false],
        ];
    }
}
