<?php

namespace Statamic\Data;

use Statamic\Contracts\Data\DataFolder as DataFolderContract;

abstract class DataFolder implements DataFolderContract
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @inheritdoc
     */
    public function get($key, $default = null)
    {
        return array_get($this->data, $key, $default);
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @inheritdoc
     */
    public function remove($key)
    {
        unset($this->data[$key]);
    }

    /**
     * @inheritdoc
     */
    public function has($key)
    {
        return array_has($this->data, $key);
    }

    /**
     * @inheritdoc
     */
    public function data($data = null)
    {
        if (! $data) {
            return $this->data;
        }

        $this->data = $data;
    }

    /**
     * @inheritdoc
     */
    public function path($path = null)
    {
        if (is_null($path)) {
            return $this->path;
        }

        $this->path = $path;
    }

    /**
     * @inheritdoc
     */
    public function basename()
    {
        return basename($this->path());
    }

    /**
     * @inheritdoc
     */
    public function title()
    {
        return $this->get('title', $this->computedTitle());
    }

    /**
     * @inheritdoc
     */
    public function computedTitle()
    {
        return ucfirst(pathinfo($this->path())['filename']);
    }
}
