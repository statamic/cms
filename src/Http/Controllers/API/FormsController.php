<?php

namespace Statamic\Http\Controllers\API;

use Statamic\Facades\Form;
use Statamic\Http\Resources\API\FormResource;

class FormsController extends ApiController
{
    protected $endpointConfigKey = 'forms';
    protected $limitRouteResource = 'form';

    public function index()
    {
        $this->abortIfDisabled();

        return app(FormResource::class)::collection(
            $this->filterAllowedResources(Form::all())
        );
    }

    public function show($form)
    {
        $this->abortIfDisabled();

        return app(FormResource::class)::make($form);
    }
}
