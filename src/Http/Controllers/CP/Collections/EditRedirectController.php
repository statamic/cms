<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Data;
use Statamic\Http\Controllers\CP\CpController;

class EditRedirectController extends CpController
{
    public function __invoke(Request $request)
    {
        if ($data = Data::find($request->id)) {
            return redirect($data->editUrl());
        }

        throw new NotFoundHttpException;
    }
}
