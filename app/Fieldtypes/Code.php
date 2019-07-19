<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Code extends Fieldtype
{
    protected $configFields = [
        'theme' => [
            'type' => 'select',
            'default' => 'material',
            'options' => [
                'material' => 'Dark',
                'light' => 'Light'
            ],
            'instructions' => 'Choose your prefered theme.'
        ],
        'mode' => [
            'type' => 'select',
            'default' => 'htmlmixed',
            'instructions' => 'Choose language for syntax highlighting.',
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
            ]
        ],
        'indent_type' => [
            'type' => 'select',
            'default' => 'tabs',
            'options' => [
                'tabs' => 'Tabs',
                'spaces' => 'Spaces',
            ],
            'width' => 50,
            'instructions' => 'Set your preferred type of indentation.'
        ],
        'indent_size' => [
            'type' => 'integer',
            'default' => 4,
            'width' => 50,
            'instructions' => 'Set your preferred indentation size (in spaces).'
        ],
        'line_numbers' => [
            'type' => 'toggle',
            'default' => true,
            'width' => 50,
            'instructions' => 'Show line numbers.'
        ],
        'line_wrapping' => [
            'type' => 'toggle',
            'default' => true,
            'width' => 50,
            'instructions' => 'Wrap long lines of code.'
        ],
        'key_map' => [
            'type' => 'select',
            'default' => 'default',
            'options' => [
                'default' => 'Default',
                'sublime' => 'Sublime',
                'vim' => 'Vim'
            ],
            'instructions' => 'Choose preferred set of keyboard shortcuts.'
        ]
    ];
}
