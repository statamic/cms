<?php

namespace Statamic\Imaging;

use Exception;
use League\Glide\Urls\UrlBuilderFactory;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\URL;
use Statamic\Support\Str;

class GlideUrlBuilder extends ImageUrlBuilder
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @param  array  $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Build the URL.
     *
     * @param  \Statamic\Contracts\Assets\Asset|string  $item
     * @param  array  $params
     * @param  string|null  $filename
     * @return string
     *
     * @throws \Exception
     */
    public function build($item, $params, $filename = null)
    {
        $this->item = $item;

        switch ($this->itemType()) {
            case 'url':
                $path = 'http/'.base64_encode($item);
                break;
            case 'asset':
                $path = 'asset/'.base64_encode($this->item->containerId().'/'.$this->item->path());
                break;
            case 'id':
                $path = 'asset/'.base64_encode(str_replace('::', '/', $this->item));
                break;
            case 'path':
                $path = URL::encode($this->item);
                break;
            default:
                throw new Exception('Cannot build a Glide URL without a URL, path, or asset.');
        }

        $builder = UrlBuilderFactory::create($this->options['route'], $this->options['key']);

        if ($filename) {
            $path .= Str::ensureLeft(URL::encode($filename), '/');
        }

        if (isset($params['mark']) && $params['mark'] instanceof Asset) {
            $asset = $params['mark'];
            $params['mark'] = 'asset::'.base64_encode($asset->containerId().'/'.$asset->path());
        }

        return URL::prependSiteRoot($builder->getUrl($path, $params));
    }
}
