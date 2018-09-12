<?php

namespace Statamic\Fields;

use Statamic\API\Str;
use Statamic\CP\FieldtypeFactory;
use Statamic\Validation\Compiler;

class Field
{
    protected $handle;
    protected $config;

    public function __construct($handle, array $config)
    {
        $this->handle = $handle;

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

        $extra = collect($this->fieldtype()->extraRules())->map(function ($rules) {
            return $this->explodeRules($rules);
        })->all();

        return array_merge($rules, $extra);
    }

    public function attributes()
    {
        $attrs = [$this->handle => $this->display()];

        return array_merge($attrs, $this->fieldtype()->extraAttributes());
    }

    protected function explodeRules($rules)
    {
        return Compiler::explodeRules($rules);
    }
}
