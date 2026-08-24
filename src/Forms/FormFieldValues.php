<?php

namespace Statamic\Forms;

use Illuminate\Support\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Support\Arr;

class FormFieldValues
{
    public function __construct(private Entry $entry)
    {
    }

    public static function on(Entry $entry): self
    {
        return new self($entry);
    }

    public function all(): Collection
    {
        return $this->fromFields($this->entry->blueprint()->fields(), $this->entry->values()->all());
    }

    public function referencing(string $form): Collection
    {
        return $this->all()
            ->filter(fn ($value) => in_array($form, $this->handles($value), true))
            ->values();
    }

    private function fromFields(Fields $fields, array $values): Collection
    {
        return $fields->all()->values()->flatMap(
            fn (Field $field) => $this->fromField($field, Arr::get($values, $field->handle()))
        );
    }

    private function fromField(Field $field, $value): Collection
    {
        if ($value === null) {
            return collect();
        }

        return match ($field->type()) {
            'form' => collect([$value]),
            'group' => $this->fromGroup($field, $value),
            'grid' => $this->fromGrid($field, $value),
            'replicator' => $this->fromReplicator($field, $value),
            'bard' => $this->fromBard($field, $value),
            default => collect(),
        };
    }

    private function fromGroup(Field $field, $value): Collection
    {
        if (! is_array($value) || ! $fields = $field->get('fields')) {
            return collect();
        }

        return $this->fromFields(new Fields($fields), $value);
    }

    private function fromGrid(Field $field, $value): Collection
    {
        if (! is_array($value) || ! $fields = $field->get('fields')) {
            return collect();
        }

        return collect($value)
            ->filter(fn ($row) => is_array($row))
            ->flatMap(fn ($row) => $this->fromFields(new Fields($fields), $row));
    }

    private function fromReplicator(Field $field, $value): Collection
    {
        if (! is_array($value)) {
            return collect();
        }

        $sets = $field->fieldtype()->flattenedSetsConfig();

        return collect($value)
            ->filter(fn ($set) => is_array($set))
            ->flatMap(function ($set) use ($sets) {
                if (! $fields = Arr::get($sets, Arr::get($set, 'type').'.fields')) {
                    return collect();
                }

                return $this->fromFields(new Fields($fields), $set);
            });
    }

    private function fromBard(Field $field, $value): Collection
    {
        if (! is_array($value)) {
            return collect();
        }

        $sets = $field->fieldtype()->flattenedSetsConfig();

        return collect($value)
            ->filter(fn ($node) => is_array($node))
            ->flatMap(function ($node) use ($sets) {
                $values = Arr::get($node, 'attrs.values', []);

                if (! $fields = Arr::get($sets, Arr::get($values, 'type').'.fields')) {
                    return collect();
                }

                return $this->fromFields(new Fields($fields), $values);
            });
    }

    private function handles($value): array
    {
        if (is_array($value) && Arr::isAssoc($value)) {
            $value = $value['form'] ?? null;
        }

        return array_values(array_filter(Arr::wrap($value)));
    }
}
