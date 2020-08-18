<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Statamic\Http\Controllers\CP\CpController;

class BlueprintController extends CpController
{
    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure fields');
    }

    public function index()
    {
        return view('statamic::blueprints.index');
    }
}
