<?php

namespace Statamic\Fields;

use Illuminate\Support\Collection;

class FieldsetRecursionStack
{
    public function __construct(private Collection $stack)
    {
        $this->stack = collect();
    }

    public function push(string $import)
    {
        $this->stack->push($import);
    }

    public function pop()
    {
        $this->stack->pop();
    }

    public function count(): int
    {
        return $this->stack->count();
    }

    public function has(string $import): bool
    {
        return $this->stack->contains($import);
    }

    public function last()
    {
        return $this->stack->last();
    }
}
