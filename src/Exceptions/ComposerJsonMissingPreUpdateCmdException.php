<?php

namespace Statamic\Exceptions;

use Exception;
use Spatie\ErrorSolutions\Contracts\ProvidesSolution;
use Spatie\ErrorSolutions\Contracts\Solution;
use Statamic\Ignition\Solutions\EnableComposerUpdateScripts;

class ComposerJsonMissingPreUpdateCmdException extends Exception implements ProvidesSolution
{
    public function getSolution(): Solution
    {
        return new EnableComposerUpdateScripts;
    }
}
