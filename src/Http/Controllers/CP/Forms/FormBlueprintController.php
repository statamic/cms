<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Illuminate\Http\Request;
use Statamic\CP\Breadcrumbs\Breadcrumb;
use Statamic\CP\Breadcrumbs\Breadcrumbs;
use Statamic\Facades\Form;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Fields\ManagesBlueprints;

class FormBlueprintController extends CpController
{
    use ManagesBlueprints;

    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure form fields');
    }

    public function edit($form)
    {
        $blueprint = $form->blueprint();

        Breadcrumbs::push(new Breadcrumb(
            text: 'Forms',
        ));

        Breadcrumbs::push(new Breadcrumb(
            text: $form->title(),
            url: request()->url(),
            icon: 'forms',
            links: Form::all()
                ->reject(fn ($f) => $f->handle() === $form->handle())
                ->map(fn ($f) => [
                    'text' => $f->title(),
                    'icon' => 'forms',
                    'url' => cp_route('blueprints.forms.edit', $f->handle()),
                ])
                ->values()
                ->all(),
        ));

        return view('statamic::forms.blueprints.edit', [
            'form' => $form,
            'blueprint' => $blueprint,
            'blueprintVueObject' => $this->toVueObject($blueprint),
        ]);
    }

    public function update(Request $request, $form)
    {
        $request->validate(['tabs' => 'array']);

        $this->updateBlueprint($request, $form->blueprint());
    }
}
