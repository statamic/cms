<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fields;
use Statamic\Fields\FieldTransformer;
use Statamic\Fields\Fieldtype;

class NestedFields extends Fieldtype
{
    protected static $handle = 'fields';

    protected $selectable = false;

    /**
     * Converts the "fields" array of a Grid into what the <fields-fieldtype>
     * Vue component is expecting, within either the Blueprint or Fieldset
     * builders in the AJAX request performed when opening the field.
     */
    public function preProcess($fields)
    {
        return collect($fields)->map(function ($field, $i) {
            return array_merge(FieldTransformer::toVue($field), ['_id' => $i]);
        })->values()->all();
    }

    /**
     * Converts the "fields" array of a Grid field into what the
     * <grid-fieldtype> is expecting in its config.fields array.
     */
    public function preProcessConfig($config)
    {
        return (new Fields($config))->toPublishArray();
    }

    /**
     * Converts the Blueprint/Fieldset builder Settings Vue component's representation of the
     * Grid's "fields" array into what should be saved to the Blueprint/Fieldset's YAML.
     * Triggered in the AJAX request when you click "finish" when editing a Grid field.
     */
    public function process($config)
    {
        return collect($config)
            ->map(function ($field) {
                return FieldTransformer::fromVue($field);
            })
            ->values()
            ->all();
    }
}
