<?php

namespace Statamic\Addons\Replicator;

use Statamic\Extend\Fieldtype;
use Statamic\CP\FieldtypeFactory;

class ReplicatorFieldtype extends Fieldtype
{
    private $process;

    public function preProcess($data)
    {
        if (! $data) {
            return [];
        }

        $this->process = 'preProcess';

        return $this->performProcess($data);
    }

    public function process($data)
    {
        $this->process = 'process';

        return $this->performProcess($data);
    }

    private function performProcess($data)
    {
        $processed = [];

        foreach ($data as $i => $set) {
            $set_name = array_get($set, 'type');

            $set_config = array_get($this->getFieldConfig('sets'), $set_name);

            $processed[$i] = $this->processSet($set, $set_config);
        }

        return $processed;
    }

    private function processSet($set_data, $set_config)
    {
        $processed = [];

        if ($this->process === 'process') {
            unset($set_data['#hidden']);
        }

        foreach ($set_data as $field => $value) {
            if ($field === 'type') {
                $processed[$field] = $value;
                continue;
            }

            $field_config = array_get($set_config, 'fields.'.$field);

            $processed[$field] = $this->processField($value, $field_config);
        }

        return array_filter($processed);
    }

    private function processField($field_data, $field_config)
    {
        $type = array_get($field_config, 'type', 'text');

        // There's data that's not part of the fieldset.
        // Just pass it back, don't try to process it.
        if (! $field_config) {
            return $field_data;
        }

        $fieldtype = FieldtypeFactory::create($type, $field_config);

        // Either call $fieldtype->process($data) or $fieldtype->preProcess($data)
        return call_user_func([$fieldtype, $this->process], $field_data);
    }
}
