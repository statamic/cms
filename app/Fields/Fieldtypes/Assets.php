<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\API\Helper;
use Statamic\Fields\Fieldtype;

class Assets extends Fieldtype
{
    protected $categories = ['media', 'relationship'];

    protected $configFields = [
        'container' => ['type' => 'asset_container'],
        'folder' => ['type' => 'asset_folder'],
        'restrict' => ['type' => 'toggle'],
        'max_files' => ['type' => 'integer'],
        'mode' => [
            'type' => 'select',
            'options' => [
                'grid' => 'Grid',
                'list' => 'List',
            ],
        ],
    ];

    public function canHaveDefault()
    {
        return false;
    }

    public function blank()
    {
        return [];
    }

    public function preProcess($data)
    {
        $max_files = (int) $this->config('max_files');

        if ($max_files === 1 && empty($data)) {
            return $data;
        }

        return Helper::ensureArray($data);
    }

    public function process($data)
    {
        $max_files = (int) $this->config('max_files');

        if ($max_files === 1) {
            return array_get($data, 0);
        }

        return $data;
    }
}
