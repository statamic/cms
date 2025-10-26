<?php

namespace Statamic\View\Scaffolding;

use Statamic\Fields\Field;
use Statamic\View\Scaffolding\Emitters\BladeSourceEmitter;

class BladeScaffoldingContext extends ScaffoldingContext
{
    public BladeSourceEmitter $emit;

    public function __construct(
        BladeSourceEmitter $emit,
        Field $field,
        string $handle,
        string $variable,
        TemplateGenerator $generator,
        array $extra = []
    ) {
        parent::__construct($emit, $field, $handle, $variable, $generator, $extra);

        $this->emit = $emit;
    }

    public function emit(): BladeSourceEmitter
    {
        return $this->emit;
    }
}
