<?php

namespace Statamic\Fieldtypes\Bard;

use Statamic\Facades\Asset;
use Statamic\Support\Str;
use Tiptap\Core\Node;
use Tiptap\Utils\HTML;

class ImageNode extends Node
{
    public static $name = 'image';

    public function addOptions()
    {
        return [
            'HTMLAttributes' => [],
        ];
    }

    public function parseHTML()
    {
        return [
            [
                'tag' => 'img[src]',
            ],
        ];
    }

    public function addAttributes()
    {
        return [
            'src' => [
                'renderHTML' => function ($attributes) {
                    $src = $attributes->src;
                    if (! isset($src)) {
                        return null;
                    }

                    if (Str::startsWith($src, 'asset::')) {
                        $id = Str::after($src, 'asset::');
                        $src = $this->getUrl($id);
                    }

                    return [
                        'src' => $src,
                    ];
                },
            ],
            'alt' => [],
            'title' => [],
        ];
    }

    public function renderHTML($node, $HTMLAttributes = [])
    {
        return ['img', HTML::mergeAttributes($this->options['HTMLAttributes'], $HTMLAttributes), 0];
    }

    protected function getUrl($id)
    {
        return optional(Asset::find($id))->url();
    }
}
