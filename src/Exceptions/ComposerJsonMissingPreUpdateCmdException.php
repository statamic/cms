<?php

namespace Statamic\Exceptions;

use Exception;
use Spatie\ErrorSolutions\Contracts\Solution;
use Spatie\Ignition\Contracts\ProvidesSolution;
use Statamic\Ignition\Solutions\EnableComposerUpdateScripts;

class ComposerJsonMissingPreUpdateCmdException extends Exception implements ProvidesSolution
{
    public function getSolution(): Solution
    {
        return new EnableComposerUpdateScripts;
    }
}
