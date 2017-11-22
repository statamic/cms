<?php

namespace Statamic\CP\Publish;

use Statamic\API\Str;
use Statamic\API\Fieldset;
use Statamic\Contracts\CP\Fieldset as FieldsetContract;

class ValidationBuilder
{
    private $fields;
    private $fieldset;
    private $rules;
    private $attributes;

    /**
     * Create a new validation builder
     *
     * @param array            $fields    The array of fields that are being submitted
     * @param FieldsetContract $fieldset  The fieldset being used for validation
     */
    public function __construct(array $fields, FieldsetContract $fieldset)
    {
        $this->fields = $fields;
        $this->fieldset = $fieldset;
    }

    public function rules()
    {
        return $this->rules;
    }

    public function attributes()
    {
        return $this->attributes;
    }

    /**
     * Build the validation rules and attributes
     *
     * @return void
     */
    public function build()
    {
        $fieldtype_rules = $this->getFieldtypeValidationRules($this->fieldset->fieldtypes());

        $field_validation_data = $this->getFieldValidationData($this->fieldset->fields());

        $this->rules = array_merge($fieldtype_rules, $field_validation_data['rules']);
        
        $this->attributes = $field_validation_data['attributes'];
    }

    /**
     * Get validation rules from the fieldtypes
     *
     * @param  array  $fieldtypes  Array of fieldtypes
     * @param  string $name        The name of the key. Used for recursion to build the nested key.
     * @return array
     */
    private function getFieldtypeValidationRules($fieldtypes, $name = 'fields')
    {
        $rules = [];

        foreach ($fieldtypes as $fieldtype) {
            if ($fieldtype_rules = $fieldtype->rules()) {
                $rules["{$name}.{$fieldtype->getName()}"] = $fieldtype_rules;
            }

            // Grid recursion
            if ($fieldtype->getAddonClassName() === 'Grid') {
                // @todo

                // $fs = Fieldset::create('temp', $fieldtype->getFieldConfig());
                //
                // $grid_rules = $this->getFieldtypeValidationRules($fs->fieldtypes(), "{$name}.{$fieldtype->getName()}.ROW_INDEX");
                //
                // $rows = count(array_get($this->fields, Str::removeLeft("{$name}.{$fieldtype->getName()}", 'fields.'), []));
                //
                // $rules = array_merge($rules, $this->replaceRowIndexes($rows, $grid_rules));
            }

            // Replicator recursion
            if ($fieldtype->getAddonClassName() === 'Replicator') {
                // @todo
            }
        }

        return $rules;
    }

    /**
     * Get validation data from the fields
     *
     * This includes rules and attributes.
     *
     * @param  array  $fields  Array of field configs
     * @param  string $name    The name of the key. Used for recursion to build the nested key.
     * @return array
     */
    private function getFieldValidationData($fields, $name = 'fields')
    {
        $rules = [];
        $attributes = [];

        foreach ($fields as $field_name => $field_config) {
            if ($field_rules = array_get($field_config, 'validate')) {
                $rules["{$name}.{$field_name}"] = $field_rules;
            }

            // Set the attribute (ie. the display name of the field) so that validation
            // messages are words instead of dot-notated strings.
            $field_attribute = array_get($field_config, 'display', $field_name) . ' field';
            $attributes["{$name}.{$field_name}"] = $field_attribute;

            // Grid recursion
            if (array_get($field_config, 'type', 'text') === 'grid') {
                // @todo

                // $grid_data = $this->getFieldValidationData(
                //     array_get($field_config, 'fields', []),
                //     "{$name}.{$field_name}.ROW_INDEX"
                // );
                //
                // $rows = count(array_get($this->fields, Str::removeLeft("{$name}.{$field_name}", 'fields.'), []));
                //
                // $rules = array_merge($rules, $this->replaceRowIndexes($rows, $grid_data['rules']));
                //
                // $attributes = array_merge(
                //     $attributes,
                //     $this->addAttributes($this->replaceRowIndexes($rows, $grid_data['attributes']), $field_attribute)
                // );
            }

            // Replicator recursion
            if (array_get($field_config, 'type', 'text') === 'replicator') {
                // @todo
            }

        }

        return compact('rules', 'attributes');
    }

    /**
     * Replace ROW_INDEX in each rule with the index for as many rows in the submission.
     *
     * @return array
     */
    private function replaceRowIndexes($rows, $rules)
    {
        $iterations = [];

        // Create a rule for each submitted row
        // At the moment we have something like `fields.grid.ROW_INDEX.foo => 'required'` but now we'll
        // need to create a new rule for every row, replacing ROW_INDEX for each row submitted.
        foreach ($rules as $field => $rule) {
            for ($i = 0; $i < $rows; $i++) {
                $key = str_replace('ROW_INDEX', $i, $field);

                $iterations[$key] = $rule;
            }
        }

        return $iterations;
    }

    /**
     * Add attributes to an array
     *
     * Given an array of field attributes, we want to prepend an attribute. This is for
     * Grid and Replicator fields so we can see the nesting in validation messages.
     *
     * @param array  $rows
     * @param string $attribute
     */
    private function addAttributes($rows, $attribute)
    {
        $i = 0;

        return array_map(function ($row) use ($attribute, &$i) {
            $i++;
            return $attribute . ' → Row ' . $i . ' → ' . $row;
        }, $rows);
    }
}
