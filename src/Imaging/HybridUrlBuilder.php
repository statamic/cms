<?php

namespace Statamic\Imaging;

use Exception;
use League\Glide\Urls\UrlBuilderFactory;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\URL;
use Statamic\Support\Str;

class HybridUrlBuilder extends ImageUrlBuilder
{
    protected GlideCachePathResolver $resolver;

    protected array $options;

    public function __construct(GlideCachePathResolver $resolver, array $options = [])
    {
        $this->resolver = $resolver;
        $this->options = $options;
    }

    /**
     * Build the URL.
     *
     * @param  \Statamic\Contracts\Assets\Asset|string  $item
     * @param  array  $params
     * @return string
     *
     * @throws \Exception
     */
    public function build($item, $params)
    {
        $this->item = $item;

        $cachePath = $this->resolver->resolveForItem($item, $params);

        $sourceParams = match ($this->itemType()) {
            'asset' => ['asset' => Str::toBase64Url($this->item->containerId().'/'.$this->item->path())],
            'url' => ['url' => Str::toBase64Url($this->item)],
            'id' => ['asset' => Str::toBase64Url(str_replace('::', '/', $this->item))],
            'path' => ['src' => $this->item],
            default => throw new Exception('Cannot build a hybrid Glide URL without a URL, path, or asset.'),
        };

        if (isset($params['mark']) && $params['mark'] instanceof Asset) {
            $asset = $params['mark'];
            $params['mark'] = 'asset::'.Str::toBase64Url($asset->containerId().'/'.$asset->path());
        }

        $allParams = array_merge($sourceParams, $params);

        $builder = UrlBuilderFactory::create('/', $this->options['key']);

        $urlPath = URL::tidy($this->options['route'].'/'.$cachePath, withTrailingSlash: false);

        return URL::makeRelative(
            URL::prependSiteUrl($builder->getUrl($urlPath, $allParams))
        );
    }
}
