<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Charts\VerticalBar;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormValueType;
use Statamic\Forms\Fieldtypes\Number;
use Statamic\Forms\Insights\Average;
use Statamic\Forms\Insights\MinMax;
use Statamic\Forms\Summary\FieldResponses;
use Tests\TestCase;

class NumberTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new Number)->setField(new FormField('age', [
            'type' => 'number',
            'min' => 0,
            'max' => 120,
        ]));

        $this->assertEquals([
            'type' => 'integer',
            'min' => 0,
            'max' => 120,
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new Number)->setField(new FormField('quantity', [
            'type' => 'number',
            'min' => 1,
            'max' => 10,
            'default' => 1,
        ]));

        $this->assertEquals([
            'type' => 'integer',
            'min' => 1,
            'max' => 10,
            'default' => 1,
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_defaults_to_a_column_chart()
    {
        $this->assertEquals(VerticalBar::class, (new Number)->defaultChart());
    }

    #[Test]
    public function it_stores_number_values()
    {
        $this->assertSame(FormValueType::Number, (new Number)->valueType());
    }

    #[Test]
    public function it_returns_no_chart_options_so_values_are_counted()
    {
        $fieldtype = (new Number)->setField(new FormField('age', ['type' => 'number']));

        $this->assertNull($fieldtype->chartOptions($this->responses([1, 2])));
    }

    #[Test]
    public function it_defaults_to_min_max_and_average_insights()
    {
        $fieldtype = (new Number)->setField(new FormField('age', ['type' => 'number']));

        $this->assertSame([MinMax::class, Average::class], $fieldtype->defaultInsights());
        $this->assertSame([], $fieldtype->insightConfig());
    }

    private function responses(iterable $values): FieldResponses
    {
        return FieldResponses::fromValues(new FormField('field', ['type' => 'number']), $values);
    }
}
