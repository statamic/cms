<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\Facades\Site;

class SelectSiteController extends CpController
{
    public function select($handle)
    {
        Site::setSelected($handle);

        return back()->with('success', __('Site selected.'));
    }
}
