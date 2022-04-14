<?php

namespace Statamic\Http\Controllers\CP;

class SelectSiteController extends CpController
{
    public function select($handle)
    {
        session()->put('statamic.cp.selected-site', $handle);

        return back()->with('success', __('Site selected.'));
    }
}
