<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Addons\Suggest\TypeMode;

class SuggestionController extends CpController
{
    public function show(Request $request, $type)
    {
        $mode = (new TypeMode)->resolve(
            $type,
            $request->input('mode', 'options')
        );

        return $mode->setConfig($request->all())->suggestions();
    }
}
