<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Facades\Form;
use Statamic\Http\Resources\API\FormResource;

class FormsController extends ApiController
{
    public function index()
    {
        return app(FormResource::class)::collection(
            Form::all()
        );
    }

    public function show($form)
    {
        return app(FormResource::class)::make($form);
    }
}
