<?php

namespace Statamic\View\Scaffolding;

use Statamic\Fields\Field;
use Statamic\View\Scaffolding\Emitters\AbstractSourceEmitter;

abstract class ScaffoldingContext
{
    public Field $field;

    public string $handle;

    public string $variable;

    public TemplateGenerator $generator;

    public array $config;

    public array $extra;

    public function __construct(
        AbstractSourceEmitter $emit,
        Field $field,
        string $handle,
        string $variable,
        TemplateGenerator $generator,
        array $extra = []
    ) {
        $this->field = $field;
        $this->handle = $handle;
        $this->variable = $variable;
        $this->generator = $generator;
        $this->config = $field->config();
        $this->extra = $extra;
    }

    abstract public function emit(): AbstractSourceEmitter;

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->extra[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->extra);
    }

    public function fieldtype()
    {
        return $this->field->fieldtype();
    }
}
