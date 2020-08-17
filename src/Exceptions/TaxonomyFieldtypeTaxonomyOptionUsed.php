<?php

namespace Statamic\Exceptions;

use Exception;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Statamic\Statamic;

class TaxonomyFieldtypeTaxonomyOptionUsed extends Exception implements ProvidesSolution
{
    public function __construct()
    {
        parent::__construct('A taxonomy fieldtype configures its available taxonomies using the `taxonomies` option, but only found `taxonomy`.');
    }

    public function getSolution(): Solution
    {
        return BaseSolution::create('Taxonomy fields should be defined with "taxonomies".')
            ->setSolutionDescription('A field with `type: taxonomy` has used the `taxonomy` option to configure its available taxonomies. However, Statamic expects this to be `taxonomies`. In the YAML file, rename `taxonomy:` to `taxonomies:`')
            ->setDocumentationLinks([
                'Read the taxonomies guide' => Statamic::docsUrl('taxonomies'),
            ]);
    }
}
