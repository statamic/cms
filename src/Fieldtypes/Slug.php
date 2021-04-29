<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Support\Str;

class Slug extends Fieldtype
{
    protected function configFieldItems(): array
    {
        return [
            'generate' => [
                'display' => __('Generate'),
                'type' => 'toggle',
                'default' => true,
            ],
        ];
    }

    public function process($data)
    {
        if ($data !== null && $this->config('generate') === true) {
            return Str::slug($data);
        }

        return $data;
    }
}
