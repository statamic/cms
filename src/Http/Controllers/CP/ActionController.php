<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\Facades\Entry;
use Statamic\Facades\Action;
use Illuminate\Http\Request;

abstract class ActionController extends CpController
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'selections' => 'required|array',
            'context' => 'sometimes',
        ]);

        $context = isset($data['context']) ? json_decode($data['context'], true) : [];

        $items = $this->getSelectedItems(collect($data['selections']), $context);

        $actions = Action::for($this->getKey(), $context, $items);

        return $actions;
    }

    public function run(Request $request)
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
            return $action->authorize($item);
        });

        abort_unless($unauthorized->isEmpty(), 403, 'You are not authorized to run this action.');

        $action->run($items, $request->all());
    }

    abstract protected function getSelectedItems($items, $context);

    protected function getKey()
    {
        return static::$key;
    }
}
