<?php

namespace Statamic\Http\Controllers\API;

use Illuminate\Http\Request;
use Statamic\Facades\Form;
use Statamic\Http\Controllers\CP\CpController;

class GlobalsController extends CpController
{
    use TemporaryResourcePagination;

    public function index(Request $request)
    {
        // TODO: Need to think this one through more.
        return [];
    }
}
