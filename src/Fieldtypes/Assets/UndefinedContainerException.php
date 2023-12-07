<?php

namespace Statamic\Fieldtypes\Assets;

use LogicException;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\ProvidesSolution;
use Spatie\Ignition\Contracts\Solution;
use Statamic\Statamic;

class UndefinedContainerException extends LogicException implements ProvidesSolution
{
    public function __construct()
    {
        parent::__construct('An asset container has not been configured');
    }

    public function getSolution(): Solution
    {
        return BaseSolution::create('Assets fieldtype is missing the "container" option.')
            ->setSolutionDescription('Since you have multiple asset containers, you need to specify which one should be used in the field.')
            ->setDocumentationLinks(['Assets fieldtype documentation' => Statamic::docsUrl('fieldtypes/assets')]);
    }
}
