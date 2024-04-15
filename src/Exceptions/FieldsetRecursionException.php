<?php

namespace Statamic\Exceptions;

use Exception;
use Facades\Statamic\Fields\FieldsetRecursionStack;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\ProvidesSolution;
use Spatie\Ignition\Contracts\Solution;

class FieldsetRecursionException extends Exception implements ProvidesSolution
{
    public function __construct(private string $fieldset)
    {
        parent::__construct("Fieldset [$fieldset] is being imported recursively.");
    }

    public function getFieldset()
    {
        return $this->fieldset;
    }

    public function getSolution(): Solution
    {
        $last = FieldsetRecursionStack::last();

        return BaseSolution::create('Avoid infinite recursion')
            ->setSolutionDescription("The fieldset `$this->fieldset` is being imported into `$last`, however it has already been imported elsewhere. This is causing infinite recursion.");
    }
}
