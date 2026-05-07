<?php

namespace Statamic\Fields;

use Closure;
use Statamic\Support\Str;

class FieldRepository
{
    protected $fieldsets;
    protected $computedDefaultCallbacks = [];

    public function __construct(FieldsetRepository $fieldsets)
    {
        $this->fieldsets = $fieldsets;
    }

    public function find(string $field): ?Field
    {
        if (! Str::contains($field, '.')) {
            return null;
        }

        $fieldset = Str::beforeLast($field, '.');
        $handle = Str::afterLast($field, '.');

        if (! $fieldset = $this->fieldsets->find($fieldset)) {
            return null;
        }

        return $fieldset->field($handle);
    }

    public function computedDefault(string $key, Closure $callback): void
    {
        $this->computedDefaultCallbacks[$key] = $callback;
    }

    public function resolveComputedDefault(string $key, mixed $payload = null): mixed
    {
        if (! array_key_exists($key, $this->computedDefaultCallbacks)) {
            throw new \RuntimeException("No computed default registered for key [{$key}].");
        }

        return $this->computedDefaultCallbacks[$key]();
    }
}
