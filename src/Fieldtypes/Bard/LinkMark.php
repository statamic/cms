<?php

namespace Statamic\Fieldtypes\Bard;

use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Data;
use Statamic\Facades\Site;
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
            'rel' => [
                'renderHTML' => function ($attributes) {
                    return [
                        'rel' => $attributes->rel ?? '',
                    ];
                },
            ],
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

        if (! $this->isApi() && $item instanceof Entry) {
            return ($item->in(Site::current()->handle()) ?? $item)->url();
        }

        return $item->url();
    }

    private function isApi()
    {
        $isRestApi = config('statamic.api.enabled', false) && Str::startsWith(request()->path(), config('statamic.api.route', 'api'));
        $isGraphqlApi = config('statamic.graphql.enabled', false) && Str::startsWith(request()->path(), 'graphql');

        return $isRestApi || $isGraphqlApi;
    }
}
