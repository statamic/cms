<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Entry;
use Statamic\API\Action;
use Illuminate\Http\Request;
use Statamic\Fields\Validation;

abstract class ActionController extends CpController
{
    // TODO: Remove!
    public function __invoke(Request $request)
    {
        return $this->run($request);
    }

    public function index(Request $request)
    {
        $data = $request->validate([
            'selections' => 'required|array',
            'context' => 'sometimes'
        ]);

        $context = isset($data['context']) ? json_decode($data['context'], true) : [];

        $items = $this->getSelectedItems(collect($data['selections']));

        $actions = Action::for($this->getKey(), $context, $items);

        return $actions;
    }

    public function run(Request $request)
    {
        $data = $request->validate([
            'action' => 'required',
            'context' => 'required',
            'selections' => 'required|array',
        ]);

        $action = Action::get($request->action)->context($request->context);

        $validation = (new Validation)->fields($action->fields());

        $request->replace($request->values)->validate($validation->rules());

        $items = $this->getSelectedItems(collect($data['selections']));

        $unauthorized = $items->reject(function ($item) use ($action) {
            return $action->authorize($item);
        });

        abort_unless($unauthorized->isEmpty(), 403, 'You are not authorized to run this action.');

        $action->run($items, $request->all());
    }

    abstract protected function getSelectedItems($items);

    protected function getKey()
    {
        return static::$key;
    }
}
