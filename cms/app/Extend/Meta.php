<?php

namespace Statamic\Extend;

use Statamic\API\YAML;

class Meta
{
    /**
     * @var Addon
     */
    private $addon;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $data;

    /**
     * Whether the data has been loaded from disk.
     * @var bool
     */
    private $loaded = false;

    public function __construct(Addon $addon)
    {
        $this->addon = $addon;
        $this->data = collect();
    }

    public function data($data = null)
    {
        if (is_null($data)) {
            return $this->data->all();
        }

        $this->data = collect($data);

        return $this;
    }

    public function get($key, $default = null)
    {
        return $this->data->get($key, $default);
    }

    public function set($key, $value)
    {
        $this->data->put($key, $value);

        return $this;
    }

    public function has($key)
    {
        return $this->data->has($key);
    }

    public function exists()
    {
        return $this->addon->hasFile('meta.yaml');
    }

    public function load()
    {
        if ($this->exists()) {
            $contents = $this->addon->getFile('meta.yaml');
            $this->data = collect(YAML::parse($contents));
        }

        $this->loaded = true;

        return $this;
    }

    public function isLoaded()
    {
        return $this->loaded;
    }

    public function save()
    {
        $contents = YAML::dump($this->data->all());

        $this->addon->putFile('meta.yaml', $contents);
    }
}
