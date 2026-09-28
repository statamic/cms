<?php

namespace Tests\View\Scaffolding;

use Statamic\Fields\Field;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Statamic\View\Scaffolding\TemplateGenerator;
use Tests\TestCase;

abstract class ScaffoldingTestCase extends TestCase
{
    protected array $field = [];

    protected array $generatorConfig = [
        'line_ending' => 'auto',
        'indent_type' => 'space',
        'indent_size' => 4,
        'final_newline' => false,
        'use_components' => false,
    ];

    protected function configureTemplateGenerator(TemplateGenerator $generator)
    {
        if (isset($this->generatorConfig['line_ending'])) {
            $generator->lineEnding($this->generatorConfig['line_ending']);
        }

        if (isset($this->generatorConfig['indent_type'])) {
            $generator->indentType($this->generatorConfig['indent_type']);
        }

        if (isset($this->generatorConfig['indent_size'])) {
            $generator->indentSize($this->generatorConfig['indent_size']);
        }

        if (isset($this->generatorConfig['final_newline'])) {
            $generator->finalNewline($this->generatorConfig['final_newline']);
        }

        if (isset($this->generatorConfig['use_components'])) {
            $generator->preferComponentSyntax($this->generatorConfig['use_components']);
        }
    }

    protected function field(array $config = [])
    {
        return new Field('test', array_merge($this->field, $config));
    }

    protected function nestedField(array $config = [])
    {
        return $this->makeNestedField([
            'handle' => 'test',
            'field' => array_merge($this->field, $config),
        ]);
    }

    protected function preferAntlersComponents()
    {
        $this->generatorConfig['use_components'] = true;

        return $this;
    }

    protected function scaffoldAntlersField($field)
    {
        $generator = new TemplateGenerator;
        $generator->withCoreGenerators();

        $this->configureTemplateGenerator($generator);

        return StringUtilities::normalizeLineEndings(
            $generator->scaffoldField($field)
        );
    }

    protected function scaffoldBladeField($field)
    {
        $generator = new TemplateGenerator;
        $generator
            ->withCoreGenerators()
            ->templateLanguage('blade');

        $this->configureTemplateGenerator($generator);

        return StringUtilities::normalizeLineEndings(
            $generator->scaffoldField($field)
        );
    }

    protected function makeNestedField(array $field)
    {
        return new Field('root', [
            'type' => 'group',
            'fields' => [
                [
                    'handle' => 'nested_group',
                    'field' => [
                        'type' => 'group',
                        'fields' => [
                            $field,
                        ],
                    ],
                ],
            ],
        ]);
    }
}
