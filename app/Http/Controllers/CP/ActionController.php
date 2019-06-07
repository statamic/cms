<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Entry;
use Statamic\API\Action;
use Illuminate\Http\Request;
use Statamic\Fields\Validation;

abstract class ActionController extends CpController
{
    public function __invoke(Request $request)
    {
        $posted = $request->validate([
            'action' => 'required',
            'context' => 'required',
            'selections' => 'required|array',
        ]);

        $action = Action::get($request->action)->context($request->context);

        if (! $action->authorize($this->getKey(), $request->context)) {
            abort(403, 'You are not authorized to run this action');
        }

        $validation = (new Validation)->fields($action->fields());

        $request->replace($request->values)->validate($validation->rules());

        $action->run(
            $this->getSelectedItems(collect($posted['selections'])),
            $request->all()
        );
    }

    abstract protected function getSelectedItems($items);

    protected function getKey()
    {
        return static::$key;
    }
}
