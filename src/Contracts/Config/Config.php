<?php

namespace Statamic\Contracts\Config;

interface Config
{
    /**
     * Populate the config object with data.
     *
     * @param  array  $config
     * @return void
     */
    public function hydrate(array $config);

    /**
     * Get a config value.
     *
     * @param  string  $key
     * @param  bool  $default
     * @return mixed
     */
    public function get($key, $default = false);

    /**
     * Set a config value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function set($key, $value);

    /**
     * Get all config values.
     *
     * @return array
     */
    public function all();

    /**
     * Save the config.
     *
     * @return void
     */
    public function save();
}
