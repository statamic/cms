<?php

namespace Statamic\Exceptions;

class ComposerLockFileNotFoundException extends \Exception
{
    public function __construct($path)
    {
        parent::__construct("Could not find a composer lock file at [{$path}].");
    }
}
