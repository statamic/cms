<?php

namespace Statamic\Exceptions;

use Exception;
use Spatie\ErrorSolutions\Contracts\BaseSolution;
use Spatie\ErrorSolutions\Contracts\ProvidesSolution;
use Spatie\ErrorSolutions\Contracts\Solution;

class EntryOriginRecursionException extends Exception implements ProvidesSolution
{
    public function __construct(private ?string $entry, private ?string $origin)
    {
        parent::__construct("Entry [$entry] cannot use origin [$origin] because it would create a loop.");
    }

    public function getEntry()
    {
        return $this->entry;
    }

    public function getOrigin()
    {
        return $this->origin;
    }

    public function getSolution(): Solution
    {
        return BaseSolution::create('Avoid infinite recursion')
            ->setSolutionDescription("The entry `$this->origin` already originates from `$this->entry`, directly or through another entry. Pick an origin that isn't a descendant of this entry.");
    }
}
