<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\Ranking;
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
}
