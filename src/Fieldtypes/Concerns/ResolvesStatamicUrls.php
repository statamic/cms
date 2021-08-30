<?php

namespace Statamic\Fieldtypes\Concerns;

use Statamic\Facades\Data;

trait ResolvesStatamicUrls
{
    /**
     * Resolve `statamic://` URLs in string based markdown and html content.
     *
     * @param string|null $content
     * @return string|null
     */
    protected function resolveStatamicUrls($content)
    {
        if (! $content) {
            return $content;
        }

        return preg_replace_callback('/([("])statamic:\/\/([^()"]*)([)"])/im', function ($matches) {
            $data = Data::find($matches[2]);
            $url = $data ? $data->url() : '';

            return $matches[1].$url.$matches[3];
        }, $content);
    }
}
