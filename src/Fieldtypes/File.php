<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class File extends Fieldtype
{
    protected $defaultValue = [];

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
            'uploadUrl' => cp_route('file.upload'),
        ];
    }

    public function process($values)
    {
        return $this->config('max_files') === 1 ? collect($values)->first() : $values;
    }
}
