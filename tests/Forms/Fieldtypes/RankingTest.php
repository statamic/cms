<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Charts\RankedOptions;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormValueType;
use Statamic\Forms\Fieldtypes\Ranking;
use Statamic\Forms\Summary\FieldResponses;
use Tests\TestCase;

class RankingTest extends TestCase
{
    #[Test]
    public function it_returns_field_array()
    {
        $fieldtype = (new Ranking)->setField(new FormField('preferences', [
            'type' => 'ranking',
            'display' => 'Rank your favorites',
            'options' => [
                ['key' => 'summer', 'value' => 'Summer'],
                ['key' => 'winter', 'value' => 'Winter'],
                ['hidden' => true, 'key' => 'hidden'],
            ],
        ]));

        $array = $fieldtype->toFieldArray();

        $this->assertEquals('Rank your favorites', $array['display']);
        $this->assertEquals([
            'summer' => 'Summer',
            'winter' => 'Winter',
        ], $array['options']);
    }

    #[Test]
    public function it_defaults_to_a_ranking_chart()
    {
        $this->assertEquals(RankedOptions::class, (new Ranking)->defaultChart());
    }

    #[Test]
    public function it_stores_ranking_values()
    {
        $this->assertSame(FormValueType::Ranking, (new Ranking)->valueType());
    }

    #[Test]
    public function it_returns_its_options_as_chart_options()
    {
        $fieldtype = (new Ranking)->setField(new FormField('preferences', [
            'type' => 'ranking',
            'options' => ['spring' => 'Spring', 'summer' => 'Summer'],
        ]));

        $options = $fieldtype->chartOptions($this->responses([]));

        $this->assertEquals(['spring', 'summer'], $options->map->key->all());
        $this->assertEquals(['Spring', 'Summer'], $options->map->label->all());
    }

    private function responses(iterable $values): FieldResponses
    {
        return FieldResponses::fromValues(new FormField('field', ['type' => 'ranking']), $values);
    }
}
