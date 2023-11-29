<?php

namespace Statamic\Http\Controllers\CP\API;

use Statamic\Http\Controllers\CP\CpController;
use Statamic\View\Views;

class TemplatesController extends CpController
{
    public function index()
    {
        return Views::all();
    }
}
