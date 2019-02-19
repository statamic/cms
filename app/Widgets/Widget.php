<?php

namespace Statamic\Widgets;

use Statamic\API\Str;
use Statamic\Extend\HasTitle;
use Statamic\Extend\HasHandle;
use Statamic\Extend\RegistersItself;

abstract class Widget
{
    use RegistersItself, HasTitle, HasHandle {
        handle as protected traitHandle;
    }

    protected static $binding = 'widgets';

    protected $config;

    /**
     * Get config for use within widget.
     *
     * @param string|null $key
     * @param mixed $default
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
     * @param array $config
     * @return array
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
