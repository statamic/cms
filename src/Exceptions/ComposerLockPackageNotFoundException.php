<?php

namespace Statamic\Exceptions;

class ComposerLockPackageNotFoundException extends \Exception
{
    public function __construct($package)
    {
        parent::__construct("Could not find the [{$package}] in your composer.lock file.");
    }
}
