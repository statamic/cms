<?php

namespace Statamic\Fieldtypes\Assets;

use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use LogicException;
use Statamic\Statamic;

class ContainerException extends LogicException implements ProvidesSolution
{
    public function getSolution(): Solution
    {
        return BaseSolution::create('Assets fieldtype is missing the "container" option.')
            ->setSolutionDescription('Since you have multiple asset containers, you need to specify which one should be used in the field.')
            ->setDocumentationLinks(['Assets fieldtype documentation' => Statamic::docsUrl('fieldtypes/assets')]);
    }
}