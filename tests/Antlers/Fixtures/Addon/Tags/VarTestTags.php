<?php

namespace Tests\Antlers\Fixtures\Addon\Tags;

use Statamic\Tags\Tags;

class VarTestTags extends Tags
{
    protected static $handle = 'var_test';
    public static $var = null;

    public function index()
    {
        self::$var = $this->params->get('variable');
    }
}
