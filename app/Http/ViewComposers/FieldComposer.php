<?php

namespace Statamic\Http\ViewComposers;

use Statamic\Statamic;
use Illuminate\View\View;
use Statamic\Facades\Fieldset;

class FieldComposer
{
    const VIEWS = [
        'statamic::blueprints.edit',
        'statamic::fieldsets.edit',
    ];

    protected $fieldsetFields;

    public function compose(View $view)
    {
        Statamic::provideToScript([
            'fieldsets' => $this->fieldsets(),
            'fieldsetFields' => $this->fieldsetFields()
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

    private function fieldsetFields()
    {
        return $this->fieldsetFields = $this->fieldsetFields ?? collect(Fieldset::all())->flatMap(function ($fieldset) {
            return collect($fieldset->fields())->mapWithKeys(function ($field, $handle) use ($fieldset) {
                return [$fieldset->handle().'.'.$field->handle() => array_merge($field->toBlueprintArray(), [
                    'fieldset' => [
                        'handle' => $fieldset->handle(),
                        'title' => $fieldset->title(),
                    ]
                ])];
            });
        })->sortBy('display')->all();
    }
}
