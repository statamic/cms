<?php

namespace Statamic\Search;

use Statamic\Contracts\Data\Augmented;
use Statamic\Data\AugmentedData;
use Statamic\Support\Str;

class PlainResult extends Result
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getType(): string
    {
        return Str::after($this->data['reference'], '::');
    }

    public function getQueryableValue($field)
    {
        if ($field === 'status') {
            return $this->data['status'] ?? 'published';
        }

        return $this->data[$field] ?? null;
    }

    public function newAugmentedInstance(): Augmented
    {
        return new AugmentedData($this, $this->data);
    }
}
