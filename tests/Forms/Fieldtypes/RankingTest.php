<?php

namespace Tests\Forms\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fieldtypes\Ranking;
use Tests\TestCase;

class RankingTest extends TestCase
{
    #[Test]
    public function it_returns_field_array_with_defaults()
    {
        $fieldtype = (new Ranking)->setField(new FormField('preferences', [
            'type' => 'ranking',
        ]));

        $this->assertEquals([
            'type' => 'ranking',
            'options' => [],
        ], $fieldtype->toFieldArray());
    }

    #[Test]
    public function it_normalizes_options_and_passes_through_config()
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
    public function it_provides_an_example()
    {
        $example = (new Ranking)->example();

        $this->assertSame('Rank your favorite seasons', $example['config']['display']);
        $this->assertCount(4, $example['config']['options']);
        $this->assertEquals(['summer', 'spring', 'autumn', 'winter'], $example['value']);
    }

    #[Test]
    public function it_ignores_blank_options()
    {
        $fieldtype = (new Ranking)->setField(new FormField('preferences', [
            'type' => 'ranking',
            'options' => [
                'summer' => 'Summer',
                'null' => null,
                ['key' => null, 'value' => null],
                ['key' => '', 'value' => ''],
            ],
        ]));

        $this->assertEquals([
            'summer' => 'Summer',
        ], $fieldtype->toFieldArray()['options']);
    }

    #[Test]
    public function it_preloads_normalized_options()
    {
        $fieldtype = (new Ranking)->setField(new FormField('preferences', [
            'type' => 'ranking',
            'options' => [
                ['key' => 'summer', 'value' => 'Summer'],
                ['key' => 'winter', 'value' => 'Winter'],
            ],
        ]));

        $this->assertEquals([
            ['value' => 'summer', 'label' => 'Summer'],
            ['value' => 'winter', 'label' => 'Winter'],
        ], $fieldtype->preload()['options']);
    }
}
