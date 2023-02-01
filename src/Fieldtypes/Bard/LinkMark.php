<?php

namespace Statamic\Fieldtypes\Bard;

use Statamic\Facades\Data;
use Statamic\Support\Str;
use Tiptap\Marks\Link;

class LinkMark extends Link
{
    public function addOptions()
    {
        return [
            'HTMLAttributes' => [
                'rel' => '',
                'target' => '_blank',
            ],
        ];
    }

    public function addAttributes()
    {
        return [
            'href' => [
                'renderHTML' => function ($attributes) {
                    $href = $attributes->href;
                    if (! isset($href)) {
                        return null;
                    }

                    return [
                        'href' => $this->convertHref($href) ?? '',
                    ];
                },
            ],
            'target' => [
                'renderHTML' => function ($attributes) {
                    return [
                        'target' => $attributes->target ?? '',
                    ];
                },
            ],
            'title' => [],
        ];
    }

    protected function convertHref($href)
    {
        if (! Str::startsWith($href, 'statamic://')) {
            return $href;
        }

        $ref = Str::after($href, 'statamic://');

        if (! $item = Data::find($ref)) {
            return '';
        }

        return $item->url();
    }
}
