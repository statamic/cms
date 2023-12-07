<?php

namespace Statamic\Exceptions;

use Exception;
use Spatie\Ignition\Contracts\ProvidesSolution;
use Spatie\Ignition\Contracts\Solution;
use Statamic\Ignition\Solutions\EnableStatamicPro;

class StatamicProRequiredException extends Exception implements ProvidesSolution
{
    public function getSolution(): Solution
    {
        return new EnableStatamicPro;
    }
}
