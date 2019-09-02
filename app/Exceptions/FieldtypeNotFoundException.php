<?php

namespace Statamic\Exceptions;

use Exception;
use Facade\IgnitionContracts\Solution;
use Facade\IgnitionContracts\BaseSolution;
use Facade\Ignition\Support\StringComparator;
use Facade\IgnitionContracts\ProvidesSolution;
use Facades\Statamic\Fields\FieldtypeRepository;

class FieldtypeNotFoundException extends Exception implements ProvidesSolution
{
    protected $fieldtype;

    public function __construct($fieldtype)
    {
        parent::__construct("Fieldtype [{$fieldtype}] not found");

        $this->fieldtype = $fieldtype;
    }

    public function getSolution(): Solution
    {
        $description = ($suggestedFieldtype = $this->getSuggestedFieldtype())
            ? "Did you mean `$suggestedFieldtype`?"
            : 'Are you sure the fieldtype exists?';

        return BaseSolution::create("The {$this->fieldtype} fieldtype was not found.")
            ->setSolutionDescription($description)
            ->setDocumentationLinks([
                'Read the fieldtypes guide' => 'https://docs.statamic.com/fieldtypes',
            ]);
    }

    protected function getSuggestedFieldtype()
    {
        return StringComparator::findClosestMatch(
            FieldtypeRepository::handles()->all(),
            $this->fieldtype
        );
    }
}
