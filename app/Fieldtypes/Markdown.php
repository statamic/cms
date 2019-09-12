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
            'max_items' => 1
        ],
        'restrict' => [
            'type' => 'toggle',
            'instructions' => 'Prevent users from navigating to other Asset folders.',
        ],
        'smartypants' => [
            'type' => 'toggle',
            'default' => false,
            'instructions' => 'Automatically convert straight quotes into curly quotes, dashes into en/em-dashes, and other similar text transformations.',
        ],
        'automatic_line_breaks' => [
            'type' => 'toggle',
            'default' => true,
            'instructions' => 'Enables automatic line breaks.'
        ],
        'automatic_links' => [
            'type' => 'toggle',
            'default' => false,
            'instructions' => 'Enables automatic linking of any URLs.'
        ],
        'escape_markup' => [
            'type' => 'toggle',
            'default' => true,
            'instructions' => 'Escapes inline HTML markup (e.g. `<div>` to `&lt;div&gt;`).'
        ],

    ];

    public function augment($value)
    {
        $markdown = new \ParsedownExtra();

        $html = $markdown
                ->setBreaksEnabled($this->config('automatic_line_breaks'))
                ->setMarkupEscaped($this->config('escape_markup'))
                ->setUrlsLinked($this->config('automatic_links'))
                ->text($value);

        if ($this->config('smartypants')) {
            $html = Html::smartypants($html);
        }

        return $html;
    }
}
