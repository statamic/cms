<?php

namespace Statamic\Exceptions;

use Exception;
use Facade\Ignition\Support\StringComparator;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Statamic\Facades\Blueprint;
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
            ? "Did you mean `$suggestedBlueprint`?"
            : 'Are you sure the fieldset exists?';

        return BaseSolution::create("The {$this->fieldsetHandle} fieldset was not found.")
            ->setSolutionDescription($description)
            ->setDocumentationLinks([
                'Read the blueprints guide' => Statamic::docsUrl('/blueprints'),
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
