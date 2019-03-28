<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\API\Entry;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class RestoreEntryRevisionController extends CpController
{
    public function __invoke(Request $request, $collection, $id, $slug, $site)
    {
        if (! $entry = Entry::find($id)) {
            return $this->pageNotFound();
        }

        $entry = $entry->in($site);

        if (! $target = $entry->revision($request->revision)) {
            dd('no such revision', $request->revision);
            // todo: handle invalid revision reference
        }

        $restored = $entry->makeFromRevision($target);

        $restored->save();

        $restored
            ->makeRevision()
            ->user($request->user())
            ->message($request->message ?? false)
            ->action('restore')
            ->save();

        optional($entry->workingCopy())->delete();

        session()->flash('success', __('Revision Restored'));
    }
}
