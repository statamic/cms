<?php

namespace Statamic\Exceptions;

class AllSearchablesNotSupported extends \Exception
{
    public function __construct()
    {
        parent::__construct("'searchables' => 'all' is no longer supported. Please see the upgrade guide for more information: https://statamic.dev/getting-started/upgrade-guide/5-to-6");
    }
}
