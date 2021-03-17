<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\ArrayableString;
use Statamic\Fields\Fieldtype;

class Code extends Fieldtype
{
    protected function configFieldItems(): array
    {
        return [
            'theme' => [
                'display' => __('Display'),
                'instructions' => __('statamic::fieldtypes.code.config.theme'),
                'type' => 'select',
                'default' => 'material',
                'options' => [
                    'material' => __('Dark'),
                    'light' => __('Light'),
                ],
                'width' => 50,
            ],
            'mode' => [
                'display' => __('Mode'),
                'instructions' => __('statamic::fieldtypes.code.config.mode'),
                'type' => 'select',
                'default' => 'htmlmixed',
                'options' => [
                    'clike' => 'C-Like',
                    'css' => 'CSS',
                    'diff' => 'Diff',
                    'go' => 'Go',
                    'haml' => 'HAML',
                    'handlebars' => 'Handlebars',
                    'htmlmixed' => 'HTML',
                    'less' => 'LESS',
                    'markdown' => 'Markdown',
                    'gfm' => 'Markdown (Github Flavored)',
                    'nginx' => 'Nginx',
                    'text/x-java' => 'Java',
                    'javascript' => 'JavaScript',
                    'jsx' => 'JSX',
                    'text/x-objectivec' => 'Objective-C',
                    'php' => 'PHP',
                    'python' => 'Python',
                    'ruby' => 'Ruby',
                    'scss' => 'SCSS',
                    'shell' => 'Shell',
                    'sql' => 'SQL',
                    'twig' => 'Twig',
                    'vue' => 'Vue',
                    'xml' => 'XML',
                    'yaml-frontmatter' => 'YAML',
                ],
                'width' => 50,
            ],
            'indent_type' => [
                'display' => __('Indent Type'),
                'instructions' => __('statamic::fieldtypes.code.config.indent_type'),
                'type' => 'select',
                'default' => 'tabs',
                'options' => [
                    'tabs' => __('Tabs'),
                    'spaces' => __('Spaces'),
                ],
                'width' => 50,
            ],
            'indent_size' => [
                'display' => __('Indent Size'),
                'instructions' => __('statamic::fieldtypes.code.config.indent_size'),
                'type' => 'integer',
                'default' => 4,
                'width' => 50,
            ],
            'key_map' => [
                'display' => __('Key Mappings'),
                'instructions' => __('statamic::fieldtypes.code.config.key_map'),
                'type' => 'select',
                'default' => 'default',
                'options' => [
                    'default' => 'Default',
                    'sublime' => 'Sublime',
                    'vim' => 'Vim',
                ],
                'width' => 50,
            ],
            'line_numbers' => [
                'display' => __('Show Line Numbers'),
                'type' => 'toggle',
                'default' => true,
                'width' => 50,
            ],
            'line_wrapping' => [
                'display' => __('Enable Line Wrapping'),
                'type' => 'toggle',
                'default' => true,
                'width' => 50,
            ],
        ];
    }

    public function augment($value)
    {
        if ($value) {
            $value = str_replace('<?php', '&lt;?php', $value);
        }

        return new ArrayableString($value, ['mode' => $this->config('mode', 'htmlmixed')]);
    }

    public function toGqlType()
    {
        return [
            'type' => GraphQL::string(),
            'resolve' => function ($item, $args, $context, $info) {
                return $item->resolveGqlValue($info->fieldName)->value();
            },
        ];
    }
}
