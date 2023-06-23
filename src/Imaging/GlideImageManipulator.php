<?php

namespace Statamic\Imaging;

use Exception;
use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Imaging\ImageManipulator;
use Statamic\Contracts\Imaging\UrlBuilder;
use Statamic\Facades\Asset as AssetAPI;

class GlideImageManipulator implements ImageManipulator
{
    /**
     * The image URL builder instance.
     *
     * @var UrlBuilder
     */
    protected $builder;

    /**
     * The item to be manipulated.
     *
     * @var Asset|string
     */
    protected $item;

    /**
     * The type of item used.
     *
     * @var string
     */
    protected $item_type;

    /**
     * A custom filename.
     *
     * @var string|null
     */
    protected $filename;

    /**
     * Methods available in Glide's API.
     *
     * @var array
     */
    private $api = [
        'bg',
        'blur',
        'border',
        'bri',
        'con',
        'crop',
        'dpr',
        'filt',
        'fit',
        'flip',
        'fm',
        'gam',
        'h',
        'mark',
        'markalpha',
        'markfit',
        'markh',
        'markpad',
        'markpos',
        'markw',
        'markx',
        'marky',
        'or',
        'p',
        'pixel',
        'q',
        'sharp',
        'w',
    ];

    /**
     * Manipulation parameters.
     *
     * @var array
     */
    protected $params = [];

    /**
     * @param  UrlBuilder  $builder
     */
    public function __construct(UrlBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Set the item to be manipulated.
     *
     * @param  Asset|string  $item  The item. Can be an asset, an asset ID, a URL, or path.
     * @return $this
     */
    public function item($item)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Build the URL.
     *
     * @return mixed
     */
    public function build()
    {
        return $this->builder->build($this->item, $this->params, $this->filename);
    }

    /**
     * Set a custom filename.
     *
     * @param  string  $filename
     * @return $this
     */
    public function filename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Set the parameters.
     *
     * @param  array  $params
     * @return $this
     */
    public function params($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Get all the parameters.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set a parameter.
     *
     * @param  string  $param
     * @param  mixed  $value
     *
     * @throws \Exception
     */
    public function setParam($param, $value)
    {
        // Error out when given an unknown parameter.
        if (! in_array($param, $this->api)) {
            throw new Exception("Glide URL parameter [$param] does not exist.");
        }

        $this->params[$param] = $value;
    }

    /**
     * Unknown method calls will attempt to add a parameter.
     *
     * @param  string  $method
     * @param  array  $args
     * @return $this
     */
    public function __call($method, $args)
    {
        $this->setParam($method, $args[0]);

        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function width($value)
    {
        $this->params['w'] = $value;

        return $this;
    }

    /**
     * @param  string  $value
     * @return $this
     */
    public function height($value)
    {
        $this->params['h'] = $value;

        return $this;
    }

    /**
     * @param  string  $value
     * @return $this
     */
    public function fit($value)
    {
        if ($value == 'crop_focal') {
            $value = 'crop';

            $asset = ($this->item instanceof Asset)
                ? $this->item
                : AssetAPI::find($this->item);

            if ($asset) {
                if ($focus = $asset->get('focus')) {
                    $value .= '-'.$focus;
                }
            }
        }

        $this->params['fit'] = $value;

        return $this;
    }

    /**
     * @param  string  $value
     * @return $this
     */
    public function crop($value)
    {
        $this->params['crop'] = $value;

        return $this;
    }

    /**
     * @param  int  $pixels
     * @return $this
     */
    public function square($pixels)
    {
        $this->params['w'] = $pixels;
        $this->params['h'] = $pixels;

        return $this;
    }

    /**
     * @param  string  $value
     * @return $this
     */
    public function orient($value)
    {
        $this->params['or'] = $value;

        return $this;
    }

    /**
     * @param  string  $value
     * @return $this
     */
    public function brightness($value)
    {
        $this->params['bri'] = $value;

        return $this;
    }

    /**
     * @param  string  $value
     * @return $this
     */
    public function contrast($value)
    {
        $this->params['con'] = $value;

        return $this;
    }

    /**
     * @param  string  $value
     * @return $this
     */
    public function gamma($value)
    {
        $this->params['gam'] = $value;

        return $this;
    }

    /**
     * @param  string  $value
     * @return $this
     */
    public function sharpen($value)
    {
        $this->params['sharp'] = $value;

        return $this;
    }

    /**
     * @param  string  $value
     * @return $this
     */
    public function blur($value)
    {
        $this->params['blur'] = $value;

        return $this;
    }

    /**
     * @param  string  $value
     * @return $this
     */
    public function pixelate($value)
    {
        $this->params['pixel'] = $value;

        return $this;
    }

    /**
     * @param  string  $value
     * @return $this
     */
    public function filter($value)
    {
        $this->params['filt'] = $value;

        return $this;
    }

    /**
     * @param  string  $value
     * @return $this
     */
    public function format($value)
    {
        $this->params['fm'] = $value;

        return $this;
    }

    /**
     * @param  string  $value
     * @return $this
     */
    public function quality($value)
    {
        $this->params['q'] = $value;

        return $this;
    }

    /**
     * @param  string  $value
     * @return $this
     */
    public function preset($value)
    {
        $this->params['p'] = $value;

        return $this;
    }
}
