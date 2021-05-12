<?php

namespace Statamic\Exceptions;

use Exception;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Statamic\Statamic;

class TermsFieldtypeBothOptionsUsedException extends Exception implements ProvidesSolution
{
    public function __construct()
    {
        parent::__construct('A terms fieldtype cannot define both `taxonomy` and `taxonomies`. Use `taxonomies`.');
    }

    public function getSolution(): Solution
    {
        return BaseSolution::create('Terms fields should be defined with "taxonomies".')
            ->setSolutionDescription('A field with `type: terms` has used both the `taxonomy` and `taxonomies` options to configure its available taxonomies. However, Statamic expects this to only be `taxonomies`. In the YAML file, make sure the field only has `taxonomies`.')
            ->setDocumentationLinks([
                'Read the taxonomies guide' => Statamic::docsUrl('taxonomies'),
            ]);
    }
}
