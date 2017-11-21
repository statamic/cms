<?php

namespace Statamic\Extend\Contextual;

use Statamic\API\Storage;

class ContextualStorage extends ContextualObject
{
    /**
     * Save a key to storage
     *
     * @param string $key   Key to save under
     * @param mixed  $data  Data to cache
     */
    public function put($key, $data)
    {
        Storage::put($this->contextualize($key), $data);
    }

    /**
     * Save a key to storage as YAML
     *
     * @param string $key   Key to save under
     * @param mixed  $data  Data to cache
     */
    public function putYAML($key, $data)
    {
        Storage::putYAML($this->contextualize($key), $data);
    }

    /**
     * Save a key to storage as a serialized array
     *
     * @param string $key   Key to save under
     * @param mixed  $data  Data to cache
     */
    public function putSerialized($key, $data)
    {
        Storage::putSerialized($this->contextualize($key), $data);
    }

    /**
     * Save a key to storage as JSON
     *
     * @param string $key   Key to save under
     * @param mixed  $data  Data to cache
     */
    public function putJSON($key, $data)
    {
        Storage::putJSON($this->contextualize($key), $data);
    }

    /**
     * Get a key from the cache
     *
     * @param string $key      Key to retrieve
     * @param null   $default  Fallback data if the value doesn't exist
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Storage::get($this->contextualize($key), $default);
    }

    /**
     * Get YAML from storage
     *
     * @param string $key      Key to retrieve
     * @param null   $default  Fallback data if the value doesn't exist
     * @return mixed
     */
    public function getYAML($key, $default = null)
    {
        return Storage::getYAML($this->contextualize($key), $default);
    }

    /**
     * Get a serialized array from storage
     *
     * @param string $key      Key to retrieve
     * @param null   $default  Fallback data if the value doesn't exist
     * @return mixed
     */
    public function getSerialized($key, $default = null)
    {
        return Storage::getSerialized($this->contextualize($key), $default);
    }

    /**
     * Get JSON from storage
     *
     * @param string $key      Key to retrieve
     * @param null   $default  Fallback data if the value doesn't exist
     * @return mixed
     */
    public function getJSON($key, $default = null)
    {
        return Storage::getJSON($this->contextualize($key), $default);
    }
}
