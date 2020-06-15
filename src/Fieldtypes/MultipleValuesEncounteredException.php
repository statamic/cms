<?php

namespace Statamic\Fieldtypes;

use Exception;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;

class MultipleValuesEncounteredException extends Exception implements ProvidesSolution
{
    protected $fieldtype;

    public function __construct($fieldtype)
    {
        parent::__construct('Fieldtype expected a single value but encountered an array.');
        $this->fieldtype = $fieldtype;
    }

    public function getSolution(): Solution
    {
        $description = 'Some fieldtypes allow you to choose either a single value, or multiple values. The fieldtype must be configured to allow multiple values.';

        return BaseSolution::create("The {$this->fieldtype->handle()} fieldtype is configured for a single value but encountered multiple.")
            ->setSolutionDescription($description)
            ->setDocumentationLinks([
                "The {$this->fieldtype->handle()} fieldtype documentation" => $this->fieldtype->docsUrl(),
            ]);
    }
}
