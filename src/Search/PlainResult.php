<?php

namespace Statamic\Search;

use Statamic\Contracts\Data\Augmented;
use Statamic\Data\AugmentedData;
use Statamic\Support\Str;

class PlainResult extends Result
{
    public function __construct(array $data)
    {
        $this->setRawResult($data);
    }

    public function getType(): string
    {
        return Str::after($this->result['reference'], '::');
    }

    public function getQueryableValue($field)
    {
        if ($field === 'status') {
            return $this->result['status'] ?? 'published';
        }

        return $this->result[$field] ?? null;
    }

    public function newAugmentedInstance(): Augmented
    {
        return new AugmentedData($this, $this->result);
    }

    public function get($key, $fallback = null)
    {
        return $this->result[$key] ?? $fallback;
    }

    public function setSupplement($key, $value)
    {
        $this->result[$key] = $value;
    }
}
