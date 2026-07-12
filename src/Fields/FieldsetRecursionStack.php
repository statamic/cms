<?php

namespace Statamic\Fields;

use Illuminate\Support\Collection;
use Statamic\Exceptions\FieldsetRecursionException;

class FieldsetRecursionStack
{
    public function __construct(private Collection $stack)
    {
        $this->stack = collect();
    }

    public function push(string $import)
    {
        if ($this->stack->contains($import)) {
            throw new FieldsetRecursionException($import, $this->stack->last());
        }

        $this->stack->push($import);
    }

    public function pop()
    {
        $this->stack->pop();
    }
}
