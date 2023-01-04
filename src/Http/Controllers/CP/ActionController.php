<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Statamic\Facades\Action;
use Statamic\Facades\User;
use Symfony\Component\HttpFoundation\Response;

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

        $items = $this->getSelectedItems(collect($data['selections']), $context);

        $action = Action::get($request->action)->context($context)->items($items);

        $validation = $action->fields()->validator();

        $request->replace($request->values)->validate($validation->rules());

        $unauthorized = $items->reject(function ($item) use ($action) {
            return $action->authorize(User::current(), $item);
        });

        abort_unless($unauthorized->isEmpty(), 403, __('You are not authorized to run this action.'));

        $values = $action->fields()->addValues($request->all())->process()->values()->all();

        $response = $action->run($items, $values);

        if ($redirect = $action->redirect($items, $values)) {
            return ['redirect' => $redirect];
        } elseif ($download = $action->download($items, $values)) {
            return $download instanceof Response ? $download : response()->download($download);
        }

        if (is_string($response)) {
            return ['message' => $response];
        }

        return $response ?: [];
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
