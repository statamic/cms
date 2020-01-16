<?php

namespace Statamic\Http\Controllers\API;

use Illuminate\Http\Request;
use Statamic\Facades\GlobalSet;
use Statamic\Http\Resources\API\GlobalSetResource;

class GlobalsController extends ApiController
{
    public function index(Request $request)
    {
        return app(GlobalSetResource::class)::collection(
            GlobalSet::all()
        );
    }

    public function show($globalSet)
    {
        return app(GlobalSetResource::class)::make($globalSet);
    }
}
