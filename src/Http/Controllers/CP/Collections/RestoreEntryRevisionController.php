<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Revisions\Revision;

use function Statamic\trans as __;

class RestoreEntryRevisionController extends CpController
{
    public function __invoke(Request $request, $collection, $entry)
    {
        if (User::current()->cant('edit', $entry)) {
            abort(403);
        }

        /** @var $target Revision */
        if (! $target = $entry->revision($request->revision)) {
            abort(404, "Revision [{$request->revision}] not found.");
        }

        if ($entry->published()) {
            $target->toWorkingCopy()->date(now())->save();
        } else {
            $entry->makeFromRevision($target)->published(false)->save();
        }

        session()->flash('success', __('Revision restored'));
    }
}
