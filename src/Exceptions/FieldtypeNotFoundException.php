<?php

namespace Statamic\Exceptions;

use Exception;
use Facades\Statamic\Fields\FieldtypeRepository;
use Spatie\ErrorSolutions\Contracts\BaseSolution;
use Spatie\ErrorSolutions\Contracts\ProvidesSolution;
use Spatie\ErrorSolutions\Contracts\Solution;
use Spatie\ErrorSolutions\Support\Laravel\StringComparator;
use Statamic\Statamic;

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
        $docs = ['Read the fieldtypes guide' => Statamic::docsUrl('fieldtypes')];

        $description = ($suggestedFieldtype = $this->getSuggestedFieldtype())
            ? "Did you mean `$suggestedFieldtype`?"
            : 'Are you sure the fieldtype exists?';

        if ($suggestedFieldtype) {
            $docs["Read the $suggestedFieldtype fieldtype guide"] = Statamic::docsUrl('fieldtypes/'.$suggestedFieldtype);
        }

        return BaseSolution::create("The {$this->fieldtype} fieldtype was not found.")
            ->setSolutionDescription($description)
            ->setDocumentationLinks($docs);
    }

    protected function getSuggestedFieldtype()
    {
        if (in_array($this->fieldtype, ['relationship', 'collection'])) {
            return 'entries';
        }

        if (in_array($this->fieldtype, ['taxonomy'])) {
            return 'terms';
        }

        return StringComparator::findClosestMatch(
            FieldtypeRepository::handles()->all(),
            $this->fieldtype
        );
    }
}
