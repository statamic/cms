<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Statamic\Facades\Action;
use Statamic\Facades\User;

abstract class ActionController extends CpController
{
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
            return $action->authorize(User::current(), $item);
        });

        abort_unless($unauthorized->isEmpty(), 403, __('You are not authorized to run this action.'));

        $action->run($items, $values = $request->all());

        if ($redirect = $action->redirect($items, $values)) {
            return ['redirect' => $redirect];
        } elseif ($download = $action->download($items, $values)) {
            return $download instanceof Response ? $download : response()->download($download);
        }

        return [];
    }

    public function bulkActions(Request $request)
    {
        $data = $request->validate([
            'selections' => 'required|array',
            'context' => 'sometimes',
        ]);

        $context = $data['context'] ?? [];

        $items = $this->getSelectedItems(collect($data['selections']), $context);

        return Action::forBulk($items, $context);
    }

    abstract protected function getSelectedItems($items, $context);
}
