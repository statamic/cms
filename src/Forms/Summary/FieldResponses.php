<?php

namespace Statamic\Forms\Summary;

use Illuminate\Support\Collection;
use Statamic\Forms\Fields\FormField;

final class FieldResponses
{
    private ?NumericSummary $numeric = null;
    private bool $numericResolved = false;

    /** @internal Build via FieldResponseCounter or fromValues(). */
    public function __construct(
        private FormField $field,
        private int $total,
        private array $counts,
        private array $positions,
    ) {
    }

    public static function fromValues(FormField $field, iterable $values): self
    {
        $counter = new FieldResponseCounter($field);

        foreach ($values as $value) {
            $counter->add($value);
        }

        return $counter->responses();
    }

    public static function key(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }

    public function field(): FormField
    {
        return $this->field;
    }

    public function total(): int
    {
        return $this->total;
    }

    /**
     * Counts keyed by value, in the order values were first seen.
     *
     * List values are flattened one level, so each item is counted on its own. No core
     * fieldtype stores nested arrays. PHP turns numeric-string keys into ints.
     */
    public function counts(): Collection
    {
        return collect($this->counts);
    }

    public function positions(): Collection
    {
        return collect($this->positions);
    }

    public function numeric(): ?NumericSummary
    {
        if (! $this->numericResolved) {
            $this->numeric = NumericSummary::fromCounts($this->counts);
            $this->numericResolved = true;
        }

        return $this->numeric;
    }
}
