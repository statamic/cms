<?php

namespace Statamic\Widgets;

use Statamic\Extend\HasAliases;
use Statamic\Extend\HasHandle;
use Statamic\Extend\HasTitle;
use Statamic\Extend\RegistersItself;
use Statamic\Support\Str;

abstract class Widget
{
    use HasAliases, HasHandle, HasTitle, RegistersItself {
        handle as protected traitHandle;
    }

    protected static $binding = 'widgets';

    protected $config;

    /**
     * Get config for use within widget.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return string|\Illuminate\Support\Collection
     */
    public function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return collect($this->config);
        }

        return $this->config[$key] ?? $default;
    }

    /**
     * Set config when loading widget.
     *
     * @param  array  $config
     */
    public function setConfig($config)
    {
        $this->config = $config ?? [];
    }

    /**
     * Get container handle.
     *
     * @return string
     */
    public static function handle()
    {
        return Str::removeRight(static::traitHandle(), '_widget');
    }
}
