<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\Facades\Site;

class SelectSiteController extends CpController
{
    public function select($handle)
    {
        if (! $site = Site::get($handle)) {
            return back()->withError('Invalid site.');
        }

        $this->authorize('view', $site);

        Site::setSelected($handle);

        return back()->with('success', __('Site selected.'));
    }
}
