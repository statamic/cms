<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Entry;
use Statamic\API\Action;
use Illuminate\Http\Request;

class EntryActionController extends CpController
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'action' => 'required',
            'selections' => 'required|array'
        ]);

        Action::get($request->action)->run(
            $this->getSelectedItems(collect($request->selections))
        );
    }

    protected function getSelectedItems($items)
    {
        return $items->map(function ($item) {
            return Entry::find($item);
        });
    }
}
