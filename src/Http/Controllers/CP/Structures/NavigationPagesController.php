<?php

namespace Statamic\Http\Controllers\CP\Structures;

use Illuminate\Http\Request;
use Statamic\Facades\Nav;
use Statamic\Http\Controllers\CP\CpController;

class NavigationPagesController extends CpController
{
    public function update(Request $request, $nav)
    {
        $nav = Nav::find($nav);

        $blueprint = $nav->blueprint();

        if ($request->type === 'url') {
            $blueprint->ensureField('title', ['display' => __('Title'), 'validate' => 'required_without:url']);
            $blueprint->ensureField('url', ['display' => __('URL'), 'validate' => 'required_without:title']);
        }

        $blueprint->fields()->addValues($request->values)->validate();
    }
}
