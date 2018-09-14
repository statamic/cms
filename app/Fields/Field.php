<?php

namespace Statamic\Fields;

use Statamic\API\Str;
use Statamic\CP\FieldtypeFactory;
use Facades\Statamic\Fields\FieldtypeRepository;

class Field
{
    protected $handle;
    protected $config;

    public function __construct($handle, array $config)
    {
        $this->handle = $handle;

        $this->config = array_merge($config, [
            'handle' => $this->handle
        ]);
    }

    public function setHandle(string $handle)
    {
        $this->handle = $handle;

        return $this;
    }

    public function handle()
    {
        return $this->handle;
    }

    public function type()
    {
        return array_get($this->config, 'type', 'text');
    }

    public function fieldtype()
    {
        return FieldtypeRepository::find($this->type());
    }

    public function display()
    {
        return array_get($this->config, 'display', Str::humanize($this->handle));
    }

    public function instructions()
    {
        return array_get($this->config, 'instructions');
    }

    public function rules()
    {
        $rules = [$this->handle => array_merge(
            Validation::explodeRules(array_get($this->config, 'validate')),
            Validation::explodeRules($this->fieldtype()->rules())
        )];

        $extra = collect($this->fieldtype()->extraRules(null))->map(function ($rules) {
            return Validation::explodeRules($rules);
        })->all();

        return array_merge($rules, $extra);
    }

    public function isRequired()
    {
        return collect($this->rules()[$this->handle])->contains('required');
    }

    public function toPublishArray()
    {
        return [
            'handle' => $this->handle,
            'type' => $this->type(),
            'display' => $this->display(),
            'instructions' => $this->instructions(),
            'required' => $this->isRequired(),
        ];
    }
}
