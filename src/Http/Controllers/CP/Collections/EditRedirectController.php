<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Statamic\Http\Controllers\CP\CpController;

class EditRedirectController extends CpController
{
    public function __invoke(Request $request)
    {
        return redirect(
            Entry::findOrFail($request->id)->editUrl()
        );
    }
}
