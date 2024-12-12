<?php

namespace Statamic\Imaging;

use Statamic\Facades\Asset;
use Statamic\Facades\URL;
use Statamic\Imaging\Manipulators\GlideManipulator;
use Statamic\Support\Str;

/**
 * @deprecated URL building is now done in dedicated manipulator classes.
 * @see GlideManipulator
 */
class StaticUrlBuilder extends ImageUrlBuilder
{
    /**
     * @var ImageGenerator
     */
    protected $generator;

    /**
     * @var mixed
     */
    protected $item;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var array
     */
    protected $options;

    public function __construct(ImageGenerator $generator, array $options = [])
    {
        $this->generator = $generator;
        $this->options = $options;
    }

    /**
     * Build the URL.
     *
     * @param  \Statamic\Contracts\Assets\Asset|string  $item
     * @param  array  $params
     * @return string
     */
    public function build($item, $params)
    {
        $this->item = $item;
        $this->params = $params;

        $url = Str::removeRight($this->options['route'], '/').'/'.$this->generatePath();

        return URL::encode($url);
    }

    /**
     * Get the path to the generated image. It will be generated if it isn't already.
     *
     * @return string
     */
    protected function generatePath()
    {
        if (is_string($this->item) && Str::isUrl($this->item)) {
            $method = sprintf('generateBy%s', Str::startsWith($this->item, 'http') ? 'Url' : 'Path');

            return $this->generator->$method($this->item, $this->params);
        }

        $asset = ($this->itemType() === 'asset') ? $this->item : Asset::find($this->item);

        if (! $asset) {
            throw new AssetNotFoundException(
                sprintf('Could not generate a static manipulated image URL from asset [%s]', $this->item)
            );
        }

        return $this->generator->generateByAsset($asset, $this->params);
    }
}
