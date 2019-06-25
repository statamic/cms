<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Markdown extends Fieldtype
{
    protected $configFields = [
        'container' => ['type' => 'asset_container', 'max_items' => 1],
        'folder' => ['type' => 'asset_folder', 'max_items' => 1],
        'restrict' => ['type' => 'toggle'],
        'smartypants' => [
            'type' => 'toggle',
            'default' => false
        ],
        'automatic_line_breaks' => [
            'type' => 'toggle',
            'default' => true
        ],
        'escape_markup' => [
            'type' => 'toggle',
            'default' => true
        ],
        'automatic_links' => [
            'type' => 'toggle',
            'default' => false
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
            $html = smartypants($html);
        }

        return $html;
    }
}
