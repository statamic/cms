<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Statamic\Facades\Entry;
use Illuminate\Http\Request;
use Statamic\Revisions\WorkingCopy;
use Statamic\Http\Controllers\CP\CpController;

class RestoreTermRevisionController extends CpController
{
    public function __invoke(Request $request, $collection, $entry)
    {
        if (! $target = $entry->revision($request->revision)) {
            dd('no such revision', $request->revision);
            // todo: handle invalid revision reference
        }

        if ($entry->published()) {
            WorkingCopy::fromRevision($target)->date(now())->save();
        } else {
            $entry->makeFromRevision($target)->published(false)->save();
        }

        session()->flash('success', __('Revision restored'));
    }
}
