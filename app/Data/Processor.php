<?php

namespace Statamic\Data;

use Statamic\API\Fieldset;

class Processor
{
    protected $fieldset;
    protected $fieldtypes;

    /**
     * @param \Statamic\CP\Fieldset $fieldset
     */
    public function __construct($fieldset)
    {
        $this->fieldset = $fieldset;

        $this->fieldtypes = collect($fieldset->fieldtypes())->keyBy(function ($fieldtype) {
            return $fieldtype->getName();
        });
    }

    /**
     * Run data through fieldtype preprocessing.
     *
     * @param array $data
     * @return array
     */
    public function preProcess($data = [])
    {
        return collect($data)->map(function ($value, $key) {
            return ($fieldtype = $this->fieldtypes->get($key))
                ? $fieldtype->preProcess($value)
                : $value;
        })->all();
    }

    /**
     * Run data through fieldtype post processing.
     *
     * @param array $data
     * @return array
     */
    public function process($data, $filterNulls = true)
    {
        $data = collect($data)->map(function ($value, $key) {
            return ($fieldtype = $this->fieldtypes->get($key))
                ? $fieldtype->process($value)
                : $value;
        })->all();

        return $filterNulls ? $this->removeNullValues($data) : $data;
    }

    /**
     * Create an array of blank/default values for all fields in
     * the fieldset, then override with the actual data where applicable.
     *
     * @param array $data
     * @return array
     */
    public function addBlankValues($data = [])
    {
        $blanks = $this->fieldtypes->map(function ($fieldtype) {
            return $fieldtype->preProcess(
                $fieldtype->getFieldConfig('default', $fieldtype->blank())
            );
        })->all();

        return array_merge($blanks, $data);
    }

    /**
     * A shortcut to pre-process and add blank values.
     *
     * @param array $data
     * @return array
     */
    public function preProcessWithBlanks($data = [])
    {
        return $this->addBlankValues($this->preProcess($data));
    }

    /**
     * Get rid of null fields. (Empty arrays, literal null values, and empty strings)
     *
     * @param array $data
     * @return array
     */
    public function removeNullValues($data)
    {
        return array_filter($data, function ($item) {
            return is_array($item)
                ? !empty($item)
                : !in_array($item, [null, ''], true);
        });
    }
}