<?php

namespace Statamic\Tags;

use Statamic\Facades\Compare;
use Statamic\Facades\Image;

class Render extends Tags
{
    public function wildcard($tag)
    {
        $source = $this->context->value($tag);

        return $this->isPair
            ? $this->attributes($source)
            : $this->output($source);
    }

    public function index()
    {
        $source = $this->params->get('src');

        return $this->isPair
            ? $this->attributes($source)
            : $this->output($source);
    }

    private function url($source)
    {
        return $this->driver()->setSource($source)->getUrl();
    }

    private function output($source)
    {
        $url = $this->url($source);

        return $this->params->bool('tag') ? $this->imgTag($url) : $url;
    }

    private function attributes($source)
    {
        if (Compare::isQueryBuilder($source)) {
            $source = $source->get();
        }

        if (! is_iterable($source)) {
            $source = [$source];
        }

        return collect($source)->map(fn ($source) => array_merge([
            'url' => ($driver = $this->driver()->setSource($source))->getUrl(),
        ], $driver->getAttributes()));
    }

    public function batch()
    {
        $content = $this->parse();

        preg_match_all('/<img[^>]*src="([^"]*)"/i', $content, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            return $content;
        }

        $matches = collect($matches)->map(fn ($match) => [
            $match[0],
            sprintf('<img src="%s"', $this->url($match[1])),
        ])->transpose();

        return str_replace($matches[0], $matches[1], $content);
    }

    public function dataUrl()
    {
        return $this->driver()
            ->setSource($this->params->get('src'))
            ->getDataUrl();
    }

    public function dataUri()
    {
        return $this->dataUrl();
    }

    private function driver()
    {
        $driver = Image::driver();

        $allowed = $driver->getAvailableParams();

        return $driver->setParams($this->params->only($allowed)->all());
    }

    public function imgTag(string $url): string
    {
        return sprintf('<img src="%s" alt="%s" />', $url, $this->params->get('alt'));
    }
}
