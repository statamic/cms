<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\Controller;

class BaseController extends Controller
{
    public function index(Request $request)
    {
        return 'The control panel.';
    }
}
