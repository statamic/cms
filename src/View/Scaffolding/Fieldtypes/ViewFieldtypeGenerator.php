<?php

namespace Statamic\View\Scaffolding\Fieldtypes;

use Statamic\Fields\Field;
use Statamic\View\Scaffolding\TemplateGenerator;

class ViewFieldtypeGenerator
{
    public function __invoke(Field $field, TemplateGenerator $generator)
    {
        return $generator->emitFieldtypeView($field);
    }
}
