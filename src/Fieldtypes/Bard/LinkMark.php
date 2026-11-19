<?php

namespace Statamic\Fieldtypes\Bard;

use Statamic\Fieldtypes\Link as LinkFieldtype;
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
            'allowedProtocols' => [
                'http', 'https', 'ftp', 'ftps', 'mailto', 'tel', 'callto', 'sms', 'cid', 'xmpp', 'statamic', 'entry', 'asset',
            ],
            'isAllowedUri' => fn ($uri) => $this->isAllowedUri($uri),
        ];
    }

    public function addAttributes()
    {
        return [
            'href' => [
                'renderHTML' => function ($attributes) {
                    if (! isset($attributes->href)) {
                        return null;
                    }

                    return [
                        'href' => $this->convertHref($attributes->href) ?? '',
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

        $ref = str($href)->after('statamic://')->before('?')->before('#')->toString();

        $selectAcrossSites = Augmentor::$currentBardConfig['select_across_sites'] ?? false;
        $localize = ! $selectAcrossSites && ! $this->isApi();

        $item = null;

        foreach (LinkFieldtype::types() as $type) {
            if (Str::startsWith($ref, "{$type->handle()}::")) {
                $item = $type->resolve(Str::after($ref, "{$type->handle()}::"), null, $localize);
                break;
            }
        }

        if (! $item) {
            return '';
        }

        $extras = Str::after($href, $ref);

        if (! is_object($item)) {
            return $item.$extras;
        }

        return $selectAcrossSites ? $item->absoluteUrl().$extras : $item->url().$extras;
    }

    private function isApi()
    {
        $isRestApi = config('statamic.api.enabled', false) && Str::startsWith(request()->path(), config('statamic.api.route', 'api'));
        $isGraphqlApi = config('statamic.graphql.enabled', false) && Str::startsWith(request()->path(), 'graphql');

        return $isRestApi || $isGraphqlApi;
    }
}
