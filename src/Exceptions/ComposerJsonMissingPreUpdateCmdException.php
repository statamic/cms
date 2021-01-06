<?php

namespace Statamic\Exceptions;

use Exception;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Statamic\Ignition\Solutions\EnableComposerUpdateScripts;

class ComposerJsonMissingPreUpdateCmdException extends Exception implements ProvidesSolution
{
    public function getSolution(): Solution
    {
        return new EnableComposerUpdateScripts;
    }
}
