<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\ArrayableString;
use Statamic\Fields\Fieldtype;
use Statamic\GraphQL\Types\CodeType;
use Statamic\Rules\CodeFieldtypeRulers;

class Code extends Fieldtype
{
    protected $categories = ['text'];

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Appearance & Behavior'),
                'fields' => [
                    'theme' => [
                        'display' => __('Theme'),
                        'instructions' => __('statamic::fieldtypes.code.config.theme'),
                        'type' => 'select',
                        'default' => 'material',
                        'options' => [
                            'material' => __('Dark'),
                            'light' => __('Light'),
                        ],
                    ],
                    'mode' => [
                        'display' => __('Default Mode'),
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
                    ],
                    'mode_selectable' => [
                        'display' => __('Selectable Mode'),
                        'instructions' => __('statamic::fieldtypes.code.config.mode_selectable'),
                        'type' => 'toggle',
                        'default' => true,
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
                    ],
                    'indent_size' => [
                        'display' => __('Indent Size'),
                        'instructions' => __('statamic::fieldtypes.code.config.indent_size'),
                        'type' => 'integer',
                        'default' => 4,
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
                    ],
                    'line_numbers' => [
                        'display' => __('Show Line Numbers'),
                        'type' => 'toggle',
                        'default' => true,
                    ],
                    'line_wrapping' => [
                        'display' => __('Enable Line Wrapping'),
                        'type' => 'toggle',
                        'default' => true,
                    ],
                    'rulers' => [
                        'display' => __('Rulers'),
                        'instructions' => __('statamic::fieldtypes.code.config.rulers'),
                        'type' => 'array',
                        'key_header' => __('Columns'),
                        'value_header' => __('Line Style (dashed or solid)'),
                        'add_button' => __('Add Ruler'),
                        'validate' => [new CodeFieldtypeRulers],
                    ],
                ],
            ],
            [
                'display' => __('Antlers'),
                'fields' => [
                    'antlers' => [
                        'display' => __('Allow Antlers'),
                        'instructions' => __('statamic::fieldtypes.any.config.antlers'),
                        'type' => 'toggle',
                    ],

                ],
            ],
        ];
    }

    public function preProcess($value)
    {
        if (! is_array($value)) {
            $value = ['code' => $value, 'mode' => $this->mode()];
        }

        return $value;
    }

    public function preProcessConfig($value)
    {
        return $value;
    }

    public function process($value)
    {
        if (! $value) {
            return null;
        }

        if (! $this->isModeSelectable()) {
            return $value['code'];
        }

        return $value;
    }

    public function augment($value)
    {
        if (! is_array($value)) {
            $value = ['code' => $value, 'mode' => $this->mode()];
        }

        if ($value['code']) {
            $value['code'] = str_replace('<?php', '&lt;?php', $value['code']);
        }

        return new ArrayableString($code = $value['code'], [
            'code' => $code,
            'mode' => $value['mode'],
        ]);
    }

    public function toGqlType()
    {
        return [
            'type' => $this->isModeSelectable() ? GraphQL::type(CodeType::NAME) : GraphQL::string(),
            'resolve' => function ($item, $args, $context, $info) {
                $field = $item->resolveGqlValue($info->fieldName);

                return $this->isModeSelectable() && $field->value() !== null
                    ? $field->extra()
                    : $field->value();
            },
        ];
    }

    private function mode()
    {
        return $this->config('mode', 'htmlmixed');
    }

    private function isModeSelectable()
    {
        return $this->config('mode_selectable', false);
    }
}
