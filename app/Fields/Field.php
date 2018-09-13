<?php

namespace Statamic\Fields;

use Statamic\API\Str;
use Statamic\CP\FieldtypeFactory;

class Field
{
    protected $handle;
    protected $config;
    protected $data;

    public function __construct($handle, array $config, $data = null)
    {
        $this->handle = $handle;
        $this->data = $data;

        $this->config = array_merge($config, [
            'name' => $this->handle
        ]);
    }

    public function fieldtype()
    {
        $type = array_get($this->config, 'type', 'text');

        return FieldtypeFactory::create($type, $this->config);
    }

    public function display()
    {
        return array_get($this->config, 'display', Str::humanize($this->handle));
    }

    public function rules()
    {
        $rules = [$this->handle => array_merge(
            $this->explodeRules(array_get($this->config, 'validate')),
            $this->explodeRules($this->fieldtype()->rules())
        )];

        $extra = collect($this->fieldtype()->extraRules($this->data))->map(function ($rules) {
            return $this->explodeRules($rules);
        })->all();

        return array_merge($rules, $extra);
    }

    protected function explodeRules($rules)
    {
        return Validation::explodeRules($rules);
    }
}
