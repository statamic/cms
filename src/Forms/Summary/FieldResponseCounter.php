<?php

namespace Statamic\Forms\Summary;

use Statamic\Forms\Fields\FormField;
use Statamic\Support\Arr;

/** @internal */
final class FieldResponseCounter
{
    private int $total = 0;
    private array $counts = [];
    private array $positions = [];

    public function __construct(private FormField $field)
    {
    }

    public function add(mixed $value): void
    {
        if (! filled($value)) {
            return;
        }

        $this->total++;

        foreach (array_values(Arr::wrap($value)) as $index => $item) {
            if (! is_scalar($item)) {
                continue;
            }

            $key = FieldResponses::key($item);

            $this->counts[$key] = ($this->counts[$key] ?? 0) + 1;
            $this->positions[$key][$index + 1] = ($this->positions[$key][$index + 1] ?? 0) + 1;
        }
    }

    public function responses(): FieldResponses
    {
        return new FieldResponses($this->field, $this->total, $this->counts, $this->positions);
    }
}
