<?php

namespace Statamic\Forms\Charts;

readonly class ChartOption
{
    public string $label;

    public function __construct(
        public string $key,
        ?string $label = null,
        public ?string $icon = null,
        public ?string $image = null,
        public ?string $badge = null,
    ) {
        $this->label = $label ?? $key;
    }
}
