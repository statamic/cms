<?php

namespace Statamic\Assets;

class AssetFactory
{
    protected $data = [];
    protected $container;
    protected $file;
    protected $path;

    /**
     * @param null $path
     * @return $this
     */
    public function create($path = null)
    {
        if ($path) {
            $this->path($path);
        }

        return $this;
    }

    /**
     * @param string $container
     * @return $this
     */
    public function container($container)
    {
        $this->container = $container;

        return $this;
    }

    public function path($path)
    {
        $this->path = $path;

        return $this;
    }

    public function folder($folder)
    {
        throw new \Exception('Cannot set a folder. Instead, set a path to the file..');
    }

    /**
     * @param string $file
     * @return $this
     */
    public function file($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function with(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return \Statamic\Assets\Asset
     */
    public function get()
    {
        $asset = new Asset;

        if ($this->path) {
            $asset->path($this->path);
        } else {
            dd('AssetFactory@get. path wasnt provided.');
//            $asset->basename($this->file);
        }

        $asset->container($this->container);
        $asset->data($this->data);

        $asset->syncOriginal();

        return $asset;
    }
}
