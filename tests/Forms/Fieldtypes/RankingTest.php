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
        ]));

        $this->assertEquals([
            'type' => 'ranking',
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_passes_through_extra_config()
    {
        $fieldtype = (new Ranking)->setField(new FormField('preferences', [
            'type' => 'ranking',
            'display' => 'Rank your preferences',
        ]));

        $this->assertEquals([
            'type' => 'ranking',
            'display' => 'Rank your preferences',
        ], $fieldtype->toFieldArray());
    }
}
