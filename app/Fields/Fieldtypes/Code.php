<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Code extends Fieldtype
{
    protected $configFields = [
        'theme' => [
            'type' => 'select',
            'default' => 'material',
            'options' => [
                ['text' => 'Dark', 'value' => 'dark'],
                ['text' => 'Light', 'value' => 'light']
            ]
        ],
        'mode' => [
            'type' => 'select',
            'default' => 'html',
            'options' => [
                ['text' => 'C-Like', 'value' => 'clike'],
                ['text' => 'CSS', 'value' => 'css'],
                ['text' => 'Diff', 'value' => 'diff'],
                ['text' => 'Go', 'value' => 'go'],
                ['text' => 'HAML', 'value' => 'haml'],
                ['text' => 'Handlebars', 'value' => 'handlebars'],
                ['text' => 'HTML', 'value' => 'htmlmixed'],
                ['text' => 'LESS', 'value' => 'less'],
                ['text' => 'Markdown', 'value' => 'markdown'],
                ['text' => 'Markdown (Github Flavored)', 'value' => 'gfm'],
                ['text' => 'Nginx', 'value' => 'nginx'],
                ['text' => 'Java', 'value' => 'text/x-java'],
                ['text' => 'JavaScript', 'value' => 'javascript'],
                ['text' => 'JSX', 'value' => 'jsx'],
                ['text' => 'Objective-C', 'value' => 'text/x-objectivec'],
                ['text' => 'PHP', 'value' => 'php'],
                ['text' => 'Python', 'value' => 'python'],
                ['text' => 'Ruby', 'value' => 'ruby'],
                ['text' => 'SCSS', 'value' => 'scss'],
                ['text' => 'Shell', 'value' => 'shell'],
                ['text' => 'SQL', 'value' => 'sql'],
                ['text' => 'Twig', 'value' => 'twig'],
                ['text' => 'Vue', 'value' => 'vue'],
                ['text' => 'XML', 'value' => 'xml'],
                ['text' => 'YAML', 'value' => 'yaml-frontmatter'],
            ]
        ],
        'indent_type' => [
            'type' => 'select',
            'default' => 'tabs',
            'options' => [
                ['text' => 'Tabs', 'value' => 'tabs'],
                ['text' => 'Spaces', 'value' => 'spaces']
            ],
            'width' => 50
        ],
        'indent_size' => [
            'type' => 'integer',
            'default' => 4,
            'width' => 50
        ],
        'line_numbers' => [
            'type' => 'toggle',
            'default' => true,
            'width' => 50
        ],
        'line_wrapping' => [
            'type' => 'toggle',
            'default' => true,
            'width' => 50
        ],
        'key_map' => [
            'default' => 'sublime',
            'options' => [
                ['text' => 'Default', 'value' => 'default'],
                ['text' => 'Sublime', 'value' => 'sublime'],
                ['text' => 'Vim', 'value' => 'vim']
            ],
        ]
    ];
}
