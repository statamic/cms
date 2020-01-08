<?php

namespace Statamic\Fieldtypes;

use Statamic\Support\Html;
use Statamic\Fields\Fieldtype;

class Markdown extends Fieldtype
{
    protected $configFields = [
        'container' => [
            'type' => 'asset_container',
            'max_items' => 1,
            'instructions' => 'Set an optional asset container to be used for inserting images in your content.',
        ],
        'folder' => [
            'type' => 'asset_folder',
            'instructions' => 'The folder to begin browsing Assets in.',
            'max_items' => 1,
            'width' => 50,
        ],
        'restrict' => [
            'type' => 'toggle',
            'instructions' => 'Prevent users from navigating to other Asset folders.',
            'width' => 50,
        ],
        'automatic_line_breaks' => [
            'type' => 'toggle',
            'default' => true,
            'instructions' => 'Enables automatic line breaks.',
            'width' => 50,
        ],
        'automatic_links' => [
            'type' => 'toggle',
            'default' => false,
            'instructions' => 'Enables automatic linking of any URLs.',
            'width' => 50,
        ],
        'escape_markup' => [
            'type' => 'toggle',
            'default' => true,
            'instructions' => 'Escapes inline HTML markup (e.g. `<div>` to `&lt;div&gt;`).',
            'width' => 50,
        ],
        'smartypants' => [
            'type' => 'toggle',
            'default' => false,
            'instructions' => 'Automatically convert straight quotes into curly quotes, dashes into en/em-dashes, and other similar text transformations.',
            'width' => 50,
        ],

    ];

    public function augment($value)
    {
        $markdown = \Statamic\Facades\Markdown::makeParser();

        if ($this->config('automatic_line_breaks')) {
            $markdown = $markdown->withAutoLineBreaks();
        }

        if ($this->config('escape_markup')) {
            $markdown = $markdown->withMarkupEscaping();
        }

        if ($this->config('automatic_links')) {
            $markdown = $markdown->withAutoLinks();
        }

        if ($this->config('smartypants')) {
            $markdown = $markdown->withSmartPunctuation();
        }

        $html = $markdown->parse((string) $value);

        return $html;
    }

    public function preProcessIndex($value)
    {
        return $value ? Html::markdown($value) : $value;
    }
}
