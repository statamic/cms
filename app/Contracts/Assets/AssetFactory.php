<?php

namespace Statamic\Contracts\Assets;

interface AssetFactory
{
    /**
     * @param string|null $path
     * @return $this
     */
    public function create($path = null);

    /**
     * @param string $container
     * @return $this
     */
    public function container($container);

    /**
     * @param string $folder
     * @return $this
     */
    public function folder($folder);

    /**
     * @param string $file
     * @return $this
     */
    public function file($file);

    /**
     * @param $id
     * @return $this
     */
    public function id($id);

    /**
     * @param array $data
     * @return $this
     */
    public function with(array $data);

    /**
     * @param string $locale
     * @return $this
     */
    public function locale($locale);

    /**
     * @return \Statamic\Assets\Asset
     */
    public function get();
}
