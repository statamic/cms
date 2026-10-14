<?php

namespace Statamic\View\Instrumentation;

use LogicException;
use Statamic\Fields\Value;
use WeakMap;

/** @internal */
abstract class FieldTracer
{
    private bool $capturing = false;
    private bool $readingMetadata = false;
    private array $fields = [];
    private array $fieldIds = [];
    private WeakMap $values;
    private WeakMap $owners;
    private int $nextOwner = 0;

    public function capture(callable $render): mixed
    {
        if ($this->capturing) {
            throw new LogicException('A field trace capture is already active.');
        }

        $this->fields = [];
        $this->fieldIds = [];
        $this->values = new WeakMap;
        $this->owners = new WeakMap;
        $this->nextOwner = 0;
        $this->capturing = true;

        try {
            return Value::withReadObserver($this->read(...), $render);
        } finally {
            $this->capturing = false;
            $this->values = new WeakMap;
            $this->owners = new WeakMap;
            $this->fieldIds = [];
        }
    }

    public function fields(): array
    {
        return $this->fields;
    }

    private function read(Value $value): void
    {
        if ($this->readingMetadata || ! $this->isObservingReads()) {
            return;
        }

        if (! isset($this->values[$value])) {
            $this->readingMetadata = true;

            try {
                $this->values[$value] = $this->fieldId($value) ?? 0;
            } finally {
                $this->readingMetadata = false;
            }
        }

        $field = $this->values[$value];

        if ($field !== 0) {
            $this->fieldRead($field);
        }
    }

    abstract protected function isObservingReads(): bool;

    abstract protected function fieldRead(int $field): void;

    private function fieldId(Value $value): ?int
    {
        $field = $value->sourceField();

        if (! $field) {
            return null;
        }

        $owner = $value->augmentable() ?? $field->parent();

        $identity = is_object($owner) ? $owner : $field;

        if (! isset($this->owners[$identity])) {
            $this->nextOwner++;
            $reference = null;
            $locale = null;
            $blueprint = null;

            if (is_object($owner)) {
                if (method_exists($owner, 'reference')) {
                    $reference = $owner->reference();
                }

                if (method_exists($owner, 'locale')) {
                    $locale = $owner->locale();
                }

                if (method_exists($owner, 'blueprint')) {
                    $blueprint = $owner->blueprint()?->fullyQualifiedHandle();
                }
            }

            $this->owners[$identity] = [
                'id' => $this->nextOwner,
                'reference' => $reference,
                'locale' => $locale,
                'blueprint' => $blueprint,
            ];
        }

        $owner = $this->owners[$identity];
        $path = $field->sourcePathKeys();
        $reference = $owner['reference'];
        $source = $owner['id'];

        if (is_string($reference) && $reference !== '' && ! str_ends_with($reference, '::')) {
            $source = [$reference, $owner['locale'], $owner['blueprint']];
        }

        $key = json_encode([$source, $path, $field->type()]);

        if (isset($this->fieldIds[$key])) {
            return $this->fieldIds[$key];
        }

        $id = count($this->fields) + 1;
        $this->fields[$id] = [
            'reference' => $owner['reference'],
            'locale' => $owner['locale'],
            'blueprint' => $owner['blueprint'],
            'path' => $path,
            'handle' => $field->handle(),
            'display' => $field->display(),
            'type' => $field->type(),
        ];

        $this->fieldIds[$key] = $id;

        return $id;
    }
}
