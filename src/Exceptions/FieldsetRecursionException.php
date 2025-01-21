<?php

namespace Statamic\Exceptions;

use Exception;
use Spatie\ErrorSolutions\Contracts\BaseSolution;
use Spatie\ErrorSolutions\Contracts\ProvidesSolution;
use Spatie\ErrorSolutions\Contracts\Solution;

class FieldsetRecursionException extends Exception implements ProvidesSolution
{
    public function __construct(private string $fieldset, private string $target)
    {
        parent::__construct("Fieldset [$fieldset] is being imported recursively.");
    }

    public function getFieldset()
    {
        return $this->fieldset;
    }

    public function getSolution(): Solution
    {
        return BaseSolution::create('Avoid infinite recursion')
            ->setSolutionDescription("The fieldset `$this->fieldset` is being imported into `$this->target`, however it has already been imported elsewhere. This is causing infinite recursion.");
    }
}
