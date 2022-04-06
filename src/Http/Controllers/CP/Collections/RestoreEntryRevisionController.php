<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Collection;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Revisions\WorkingCopy;

class RestoreEntryRevisionController extends CpController
{
    public function __invoke(Request $request, $collection, $entry)
    {
        $collection = Collection::findByHandle($collection);
        if (! $collection) {
            $handle = func_get_arg(1);
            throw new NotFoundHttpException("Collection [$handle] not found.");
        }

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
