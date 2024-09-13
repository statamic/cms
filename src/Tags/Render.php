<?php

namespace Statamic\Tags;

use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Imaging\Manipulator;
use Statamic\Facades\Compare;
use Statamic\Facades\Image;
use Statamic\Imaging\Manipulators\Sources\Source;

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
        return $this->driver($source)->getUrl();
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

        return collect($source)->map(fn ($source) => [
            ...($source instanceof Augmentable ? $source->toAugmentedArray() : []),
            'url' => ($driver = $this->driver($source))->getUrl(),
            ...$driver->getAttributes(),
        ]);
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
        return $this
            ->driver($this->params->get('src'))
            ->getDataUrl();
    }

    public function dataUri()
    {
        return $this->dataUrl();
    }

    private function driver(mixed $source): Manipulator
    {
        $source = Source::from($source);

        $driver = ($explicit = $this->params->get('using')) ? Image::driver($explicit) : $source->manipulator();

        $params = $this->params->only($driver->getAvailableParams())->all();

        if (($params['fit'] ?? null) === 'crop_focal') {
            $driver->addFocalPointParams(...explode('-', $source->asset()->focus ?? '50-50-1'));
            unset($params['fit']);
        }

        return $driver->setSource($source)->addParams($params);
    }

    public function imgTag(string $url): string
    {
        return sprintf('<img src="%s" alt="%s" />', $url, $this->params->get('alt'));
    }
}
