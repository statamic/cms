<?php

namespace Statamic\Http\Controllers\CP;

use Exception;
use Illuminate\Http\Request;
use Statamic\Facades\Action;
use Statamic\Facades\User;
use Statamic\Support\Arr;
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

        if ($action->requiresElevatedSession()) {
            $this->requireElevatedSession();
        }

        $values = $action->fields()->addValues($request->all())->process()->values()->all();
        $successful = true;

        try {
            $response = $action->run($items, $values);
        } catch (Exception $e) {
            $response = empty($msg = $e->getMessage()) ? __('Action failed') : $msg;
            $successful = false;
        }

        if ($redirect = $action->redirect($items, $values)) {
            return [
                'redirect' => $redirect,
                'bypassesDirtyWarning' => $action->bypassesDirtyWarning(),
            ];
        } elseif ($download = $action->download($items, $values)) {
            return $download instanceof Response ? $download : response()->download($download);
        }

        if (is_string($response)) {
            $response = ['message' => $response];
        }

        $response = $response ?: [];
        $response['success'] = $successful;

        if (Arr::get($context, 'view') === 'form') {
            $response['data'] = $this->getItemData($items->first(), $context);
        }

        return $response;
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

    protected function getItemData($item, $context): array
    {
        return [];
    }
}
