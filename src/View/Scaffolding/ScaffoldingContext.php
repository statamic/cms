<?php

namespace Statamic\View\Scaffolding;

use Statamic\Fields\Field;
use Statamic\View\Scaffolding\Emitters\AbstractSourceEmitter;

/**
 * @template TEmitter of AbstractSourceEmitter
 *
 * @property TEmitter $emit
 */
abstract class ScaffoldingContext
{
    public array $config;

    /**
     * @param  TEmitter  $emit
     */
    public function __construct(
        public AbstractSourceEmitter $emit,
        public Field $field,
        public string $handle,
        public string $variable,
        public TemplateGenerator $generator,
        public array $extra = []
    ) {
        $this->config = $field->config();
    }

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
