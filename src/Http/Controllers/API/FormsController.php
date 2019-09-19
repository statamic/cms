<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Facades\Form;
use Illuminate\Http\Request;
use Statamic\Http\Resources\FormResource;
use Statamic\Http\Controllers\CP\CpController;

class FormsController extends CpController
{
    use TemporaryResourcePagination;

    public function index(Request $request)
    {
        $forms = static::paginate(Form::all());

        return FormResource::collection($forms);
    }
}
