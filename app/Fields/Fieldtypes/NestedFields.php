<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\Fields\Fields;
use Statamic\Fields\Fieldset;
use Statamic\Fields\Fieldtype;
use Facades\Statamic\Fields\FieldtypeRepository;

class NestedFields extends Fieldtype
{
    protected $selectable = false;

    public function preProcess($config)
    {
        $fields = new Fields(collect($config)->map(function ($field, $handle) {
            return compact('field', 'handle');
        }));

        return $fields->all()->map(function ($field, $handle) {
            return $field->config() + ['handle' => $handle];
        })->values()->all();
    }

    public function process($config)
    {
        return collect($config)
            ->keyBy('handle')
            ->map(function ($field) {
                return $this->processField($field);
            })->all();
    }

    private function processField($field)
    {
        $fieldtype = FieldtypeRepository::find($field['type']);

        $processed = $fieldtype->configFields()->addValues($field)->process()->values();

        return $this->clean(array_merge($field, $processed));
    }

    private function clean($field)
    {
        // TODO: Use the abstracted function instead of this class method.
        // In v2 it was in Fieldset::cleanFieldForSaving

        $field = array_except($field, ['_id', 'handle']);

        if (in_array(array_get($field, 'width'), [100, '100'])) {
            unset($field['width']);
        }

        $field = $this->discardBlankKeys($field);

        return $field;
    }

    private function discardBlankKeys($array)
    {
        // TODO: Use the abstracted function instead of this class method.
        // In v2 it was in Fieldset::discardBlankKeys

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                // Recursion!
                $array[$key] = $this->discardBlankKeys($value);
            } else {
                // Strip out nulls and empty strings. We want to keep literal false values.
                if (in_array($value, [null, ''], true)) {
                    unset($array[$key]);
                }
            }
        }

        return $array;
    }
}
