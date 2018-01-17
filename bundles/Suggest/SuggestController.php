<?php

namespace Statamic\Addons\Suggest;

use Illuminate\Http\Request;
use Statamic\Extend\Controller;

class SuggestController extends Controller
{
    /**
     * Get the suggestions
     *
     * @return array
     * @throws \Statamic\Exceptions\FatalException
     */
    public function suggestions(Request $request)
    {
        $mode = (new TypeMode)->resolve(
            $request->input('type'),
            $request->input('mode', 'options')
        );

        return $mode->setConfig($request->all())->suggestions();
    }
}
