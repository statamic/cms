<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class FieldsController extends CpController
{
    public function index(Request $request)
    {
        return view('statamic::fields.index');
    }

    public function show(Request $request)
    {
        return view('statamic::fields.create');
    }

    public function create(Request $request)
    {
        return view('statamic::fields.create');
    }

    public function edit(Request $request)
    {
        return view('statamic::fields.edit');
    }

    public function update(Request $request)
    {

    }
}
