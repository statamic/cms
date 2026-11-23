<?php

namespace Statamic\Search\Typesense;

use BackedEnum;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use JsonSerializable;
use Statamic\Sites\Site;
use Stringable;

class Normalizer
{
    public function document(array $document): array
    {
        return collect($document)
            ->mapWithKeys(function ($value, $field) {
                $field = is_string($field) ? str_replace('->', '.', $field) : $field;

                return [$field => $this->value($value)];
            })
            ->reject(fn ($value) => is_null($value))
            ->all();
    }

    public function value(mixed $value): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $value->getTimestamp();
        }

        if ($value instanceof Site) {
            return $value->handle();
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof Collection) {
            $isList = array_is_list($value->all());
            $value = $value
                ->map(fn ($item) => $this->value($item))
                ->reject(fn ($item) => is_null($item));

            return $isList ? $value->values()->all() : $value->all();
        }

        if (is_object($value) && method_exists($value, 'handle')) {
            return $this->value($value->handle());
        }

        if ($value instanceof Arrayable) {
            return $this->value($value->toArray());
        }

        if ($value instanceof JsonSerializable) {
            return $this->value($value->jsonSerialize());
        }

        if (is_array($value)) {
            $isList = array_is_list($value);
            $value = collect($value)
                ->map(fn ($item) => $this->value($item))
                ->reject(fn ($item) => is_null($item));

            return $isList ? $value->values()->all() : $value->all();
        }

        if ($value instanceof Stringable) {
            return (string) $value;
        }

        return is_scalar($value) || is_null($value) ? $value : null;
    }
}
