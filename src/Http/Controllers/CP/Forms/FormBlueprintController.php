<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Fields\ManagesBlueprints;

class FormBlueprintController extends CpController
{
    use ManagesBlueprints;

    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure fields');
    }

    public function edit($form)
    {
        $blueprint = $form->blueprint();

        return view('statamic::forms.blueprints.edit', [
            'form' => $form,
            'blueprint' => $blueprint,
            'blueprintVueObject' => $this->toVueObject($blueprint),
        ]);
    }

    public function update(Request $request, $form)
    {
        $request->validate(['sections' => 'array']);

        $this->updateBlueprint($request, $form->blueprint());
    }
}
