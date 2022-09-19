<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Files extends Fieldtype
{
    protected $defaultValue = [];
    protected $selectable = false;

    protected function configFieldItems(): array
    {
        return [
            'max_files' => [
                'display' => __('Max Files'),
                'instructions' => __('statamic::fieldtypes.assets.config.max_files'),
                'min' => 1,
                'type' => 'integer',
            ],
        ];
    }

    public function preload()
    {
        return [
            'uploadUrl' => cp_route('files.upload'),
        ];
    }

    public function process($values)
    {
        return $this->config('max_files') === 1 ? collect($values)->first() : $values;
    }

    public function rules(): array
    {
        $rules = ['array'];

        if ($max = $this->config('max_files')) {
            $rules[] = 'max:'.$max;
        }

        return $rules;
    }
}
