<?php

namespace Statamic\Extend;

use Statamic\API\Str;
use Statamic\Extend\HasTitleAndHandle;

abstract class Widget
{
    use HasTitleAndHandle {
        handle as protected traitHandle;
    }

    protected $config;

    /**
     * Get config for use within widget.
     *
     * @param mixed $key
     */
    public function config($key, $default = null)
    {
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
