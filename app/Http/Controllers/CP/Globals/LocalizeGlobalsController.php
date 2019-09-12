<?php

namespace Statamic\Http\Controllers\CP\Globals;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Facades\GlobalSet;

class LocalizeGlobalsController extends CpController
{
    public function __invoke(Request $request, $id, $handle)
    {
        if (! $set = GlobalSet::find($id)) {
            return $this->pageNotFound();
        }

        $request->validate([
            'origin' => 'required',
            'target' => 'required',
        ]);

        $localized = $set
            ->makeLocalization($target = $request->target)
            ->origin($set->in($request->origin));

        $localized->save();

        return [
            'handle' => $target,
            'url' => $localized->editUrl(),
        ];
    }
}
