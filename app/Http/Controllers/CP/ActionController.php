<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Entry;
use Statamic\API\Action;
use Illuminate\Http\Request;

abstract class ActionController extends CpController
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

    abstract protected function getSelectedItems($items);
}
