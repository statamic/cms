<?php

namespace Statamic\View\Scaffolding\Fieldtypes;

use Statamic\Fields\Field;
use Statamic\View\Scaffolding\Fieldtypes\Variables\DictionaryVariables;
use Statamic\View\Scaffolding\TemplateGenerator;

class DictionaryFieldtypeGenerator
{
    protected DictionaryVariables $variables;

    public function __construct(DictionaryVariables $variables)
    {
        $this->variables = $variables;
    }

    public function __invoke(Field $field, TemplateGenerator $generator)
    {
        $dictionaryType = $field->config()['dictionary']['type'] ?? '';
        $dictionaryVariables = $this->variables->resolve($dictionaryType);

        return $generator->emitFieldtypeView($field, [
            'variables' => $dictionaryVariables,
            'dictionaryType' => $dictionaryType,
        ]);
    }
}
