<?php

namespace Statamic\Http\View\Composers;

use Illuminate\View\View;
use Statamic\Facades\Fieldset;
use Statamic\Fields\FieldTransformer;
use Statamic\Statamic;

class FieldComposer
{
    const VIEWS = [
        'statamic::*.blueprints.edit',
        'statamic::fieldsets.edit',
    ];

    protected $fieldsetFields;

    public function compose(View $view)
    {
        Statamic::provideToScript([
            'fieldsets' => $this->fieldsets(),
            'fieldsetFields' => FieldTransformer::fieldsetFields(),
        ]);
    }

    private function fieldsets()
    {
        return Fieldset::all()->mapWithKeys(function ($fieldset) {
            return [$fieldset->handle() => [
                'handle' => $fieldset->handle(),
                'title' => $fieldset->title(),
            ]];
        });
    }
}
