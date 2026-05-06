<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Fieldtypes\Assets\DimensionsRule;
use Statamic\Fieldtypes\Assets\ImageRule;
use Statamic\Fieldtypes\Assets\MaxRule;
use Statamic\Fieldtypes\Assets\MimesRule;
use Statamic\Fieldtypes\Assets\MimetypesRule;
use Statamic\Fieldtypes\Assets\MinRule;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class Files extends Fieldtype
{
    protected $selectable = false;
    protected $selectableInForms = true;
    protected $categories = ['media'];

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

    public function preProcess($data)
    {
        return $data ?? [];
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

    public function fieldRules()
    {
        $classes = [
            'dimensions' => DimensionsRule::class,
            'image' => ImageRule::class,
            'max_filesize' => MaxRule::class,
            'mimes' => MimesRule::class,
            'mimetypes' => MimetypesRule::class,
            'min_filesize' => MinRule::class,
        ];

        return collect(parent::fieldRules())->map(function ($rule) use ($classes) {
            if (! is_string($rule)) {
                return $rule;
            }

            $name = Str::before($rule, ':');

            if ($class = Arr::get($classes, $name)) {
                $parameters = explode(',', Str::after($rule, ':'));

                return new $class($parameters);
            }

            return $rule;
        })->all();
    }
}
