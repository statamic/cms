<?php

namespace Statamic\View\Scaffolding;

use Statamic\Fields\Field;
use Statamic\View\Scaffolding\Emitters\AntlersSourceEmitter;

class AntlersScaffoldingContext extends ScaffoldingContext
{
    public AntlersSourceEmitter $emit;

    public function __construct(
        AntlersSourceEmitter $emit,
        Field $field,
        string $handle,
        string $variable,
        TemplateGenerator $generator,
        array $extra = []
    ) {
        parent::__construct($emit, $field, $handle, $variable, $generator, $extra);

        $this->emit = $emit;
    }

    public function emit(): AntlersSourceEmitter
    {
        return $this->emit;
    }
}
