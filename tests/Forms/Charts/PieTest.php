<?php

namespace Tests\Forms\Charts;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Charts\ChartOption;
use Statamic\Forms\Charts\Pie;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Summary\FieldResponses;
use Tests\TestCase;

class PieTest extends TestCase
{
    #[Test]
    public function it_keeps_the_pie_intact_while_drilled_in()
    {
        $props = (new Pie)->props($this->weightedValues(range('a', 'f')), $this->chartOptions(
            collect(range('a', 'f'))->mapWithKeys(fn ($key) => [$key => strtoupper($key)])->all()
        ));

        $this->assertEquals($props['items'], $props['drilldown']['segments']);
        $this->assertEquals(3, $props['drilldown']['focusedIndex']);
    }

    private function weightedValues(array $keys): FieldResponses
    {
        return $this->responses(collect($keys)->flatMap(fn ($key, $index) => array_fill(0, count($keys) + 2 - $index, $key)));
    }

    private function chartOptions(array $options): Collection
    {
        return collect($options)->map(fn ($label, $key) => new ChartOption((string) $key, $label))->values();
    }

    private function responses(iterable $values): FieldResponses
    {
        return FieldResponses::fromValues(new FormField('field', ['type' => 'checkboxes']), $values);
    }
}
