<?php

namespace Statamic\Exceptions;

use Exception;
use Facade\IgnitionContracts\Solution;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;

class FieldtypeNotFoundException extends Exception implements ProvidesSolution
{
    /** @return  \Facade\IgnitionContracts\Solution[] */
    public function getSolution(): Solution
    {
        return BaseSolution::create('Fieldtype not found')
            ->setSolutionDescription("It could be a typo, or perhaps you haven't created it yet?")
            ->setDocumentationLinks([
                'Fieldtypes' => 'https://docs.statamic.com/fieldtypes',
            ]);
    }
}