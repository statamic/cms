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

        $action = Action::get($request->action)->context($context);

        $validation = $action->fields()->validator();

        $request->replace($request->values)->validate($validation->rules());

        $items = $this->getSelectedItems(collect($data['selections']), $context);

        $unauthorized = $items->reject(function ($item) use ($action) {
            return $action->authorize(User::current(), $item);
        });

        abort_unless($unauthorized->isEmpty(), 403, __('You are not authorized to run this action.'));

        $response = $action->run($items, $values = $request->all());

        if ($redirect = $action->redirect($items, $values)) {
            return ['redirect' => $redirect];
        /*
        } elseif (in_array($action->handle(), ['unpublish', 'publish'])) {
            // TODO Refresh the current edit page? Seems bit rubbish - probably need to update published state using Vue
            return ['redirect' => true];
        } elseif ($action->handle() === 'delete') {
            // TODO Return back to the index screen
            return ['redirect' => route('statamic.cp.collections.index')];
        */
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
