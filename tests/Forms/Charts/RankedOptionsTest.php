<?php

namespace Tests\Forms\Charts;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Forms\Charts\ChartOption;
use Statamic\Forms\Charts\RankedOptions;
use Tests\TestCase;

class RankedOptionsTest extends TestCase
{
    #[Test]
    public function it_ranks_options_by_average_position()
    {
        $props = (new RankedOptions)->props(
            collect([
                ['summer', 'spring', 'winter'],
                ['summer', 'winter', 'spring'],
                ['spring', 'summer', 'winter'],
            ]),
            $this->chartOptions(['spring' => 'Spring', 'summer' => 'Summer', 'winter' => 'Winter'])
        );

        $this->assertEquals([
            ['key' => 'summer', 'label' => 'Summer', 'rank' => 1, 'count' => 2, 'percent' => 89],
            ['key' => 'spring', 'label' => 'Spring', 'rank' => 2, 'count' => 1, 'percent' => 67],
            ['key' => 'winter', 'label' => 'Winter', 'rank' => 3, 'count' => 0, 'percent' => 44],
        ], $props['items']);
    }

    private function chartOptions(array $options): Collection
    {
        return collect($options)->map(fn ($label, $key) => new ChartOption((string) $key, $label))->values();
    }
}
