<?php

namespace Statamic\Http\View\Composers;

use Illuminate\Support\Arr;
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

    public function compose()
    {
        Statamic::provideToScript([
            'fieldsets' => $this->fieldsets(),
        ]);
    }

    private function fieldsets()
    {
        return Fieldset::all()->mapWithKeys(function ($fieldset) {
            return [$fieldset->handle() => [
                'handle' => $fieldset->handle(),
                'title' => $fieldset->title(),
                'fields' => collect(Arr::get($fieldset->contents(), 'fields'))->map(function ($field) {
                    return FieldTransformer::toVue($field);
                })->sortBy('config.display')->values()->all(),
            ]];
        })->sortBy('title');
    }
}
