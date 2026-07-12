<?php

namespace App\Tags;

use Statamic\Tags\Tags;

class AppTestTag extends Tags
{
    protected static $handle = 'app_test_tag';

    public function index()
    {
        return 'app-tag-ok';
    }
}
