<?php

namespace Statamic\Http\Controllers\CP;

class SelectSiteController extends CpController
{
    public function select($handle)
    {
        cache()->forever('statamic.cp.selected-site', $handle);

        return back()->with('success', 'Site selected.');
    }
}
