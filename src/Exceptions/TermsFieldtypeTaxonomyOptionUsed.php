<?php

namespace Statamic\Exceptions;

use Exception;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Statamic\Statamic;

class TermsFieldtypeTaxonomyOptionUsed extends Exception implements ProvidesSolution
{
    public function __construct()
    {
        parent::__construct('A terms fieldtype configures its available taxonomies using the `taxonomies` option, but only found `taxonomy`.');
    }

    public function getSolution(): Solution
    {
        return BaseSolution::create('Terms fields should be defined with "taxonomies".')
            ->setSolutionDescription('A field with `type: terms` has used the `taxonomy` option to configure its available taxonomies. However, Statamic expects this to be `taxonomies`. In the YAML file, rename `taxonomy:` to `taxonomies:`')
            ->setDocumentationLinks([
                'Read the taxonomies guide' => Statamic::docsUrl('taxonomies'),
            ]);
    }
}
