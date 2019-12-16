<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Facades\Action;
use Statamic\Facades\Entry;
use Statamic\Facades\User;

abstract class ActionController extends CpController
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'action' => 'required',
            'selections' => 'required|array',
            'context' => 'sometimes',
        ]);

        $context = $data['context'] ?? [];

        $action = Action::get($request->action)->context($context);

        $validation = $action->fields()->validator();

        $request->replace($request->values)->validate($validation->rules());

        $items = $this->getSelectedItems(collect($data['selections']), $context);

        $unauthorized = $items->reject(function ($item) use ($action) {
            return $action->authorize(User::current(), $item);
        });

        abort_unless($unauthorized->isEmpty(), 403, 'You are not authorized to run this action.');

        $action->run($items, $request->all());
    }

    abstract protected function getSelectedItems($items, $context);
}
