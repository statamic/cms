<?php

namespace Statamic\Fields;

use Statamic\API\Str;
use Statamic\CP\FieldtypeFactory;
use Illuminate\Contracts\Support\Arrayable;
use Facades\Statamic\Fields\FieldtypeRepository;

class Field implements Arrayable
{
    protected $handle;
    protected $config;
    protected $value;

    public function __construct($handle, array $config)
    {
        $this->handle = $handle;
        $this->config = $config;
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

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function value()
    {
        return $this->value;
    }

    public function defaultValue()
    {
        return $this->config['default'] ?? $this->fieldtype()->defaultValue();
    }

    public function process()
    {
        $this->value = $this->fieldtype()->process($this->value);

        return $this;
    }

    public function preProcess()
    {
        $value = $this->value ?? $this->defaultValue();

        $this->value = $this->fieldtype()->preProcess($value);

        return $this;
    }

    public function toArray()
    {
        return array_merge($this->config, [
            'handle' => $this->handle
        ]);
    }
}
