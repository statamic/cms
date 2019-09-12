<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Facades\Form;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Resources\CollectionResource;

class GlobalsController extends CpController
{
    use TemporaryResourcePagination;

    public function index(Request $request)
    {
        // TODO: Need to think this one through more.
        return [];
    }
}
