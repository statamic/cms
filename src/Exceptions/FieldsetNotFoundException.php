<?php

namespace Statamic\Exceptions;

use Exception;
use Spatie\ErrorSolutions\Contracts\BaseSolution;
use Spatie\ErrorSolutions\Contracts\ProvidesSolution;
use Spatie\ErrorSolutions\Contracts\Solution;
use Spatie\ErrorSolutions\Support\Laravel\StringComparator;
use Statamic\Facades\Fieldset;
use Statamic\Statamic;

class FieldsetNotFoundException extends Exception implements ProvidesSolution
{
    protected $fieldsetHandle;

    public function __construct($fieldsetHandle)
    {
        parent::__construct("Fieldset [{$fieldsetHandle}] not found");

        $this->fieldsetHandle = $fieldsetHandle;
    }

    public function getSolution(): Solution
    {
        $description = ($suggestedFieldset = $this->getSuggestedFieldset())
            ? "Did you mean `$suggestedFieldset`?"
            : 'Are you sure the fieldset exists?';

        return BaseSolution::create("The {$this->fieldsetHandle} fieldset was not found.")
            ->setSolutionDescription($description)
            ->setDocumentationLinks([
                'Read the fieldsets guide' => Statamic::docsUrl('/fieldsets'),
            ]);
    }

    protected function getSuggestedFieldset()
    {
        return StringComparator::findClosestMatch(
            Fieldset::all()->map->handle()->values()->all(),
            $this->fieldsetHandle
        );
    }
}
