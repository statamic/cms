<?php

namespace Statamic\View\Antlers\Language\Runtime\Libraries\Internal;

use Statamic\View\Antlers\Language\Runtime\Libraries\RuntimeLibrary;

class PathLibrary extends RuntimeLibrary
{
    protected $name = 'path';
    protected $isRuntimeProtected = true;

    public function __construct()
    {
        $pathParam = $this->stringVarWithDefault('path', '');

        $this->exposedMethods = [
            'resource' => [$pathParam],
            'storage' => [$pathParam],
            'app' => [$pathParam],
            'base' => [$pathParam],
            'config' => [$pathParam],
            'database' => [$pathParam],
            'public' => [$pathParam],
            'statamic' => [$this->stringVarWithDefault('path', null)],
        ];
    }

    public function resource($path)
    {
        return resource_path($path);
    }

    public function storage($path)
    {
        return storage_path($path);
    }

    public function app($path)
    {
        return app_path($path);
    }

    public function base($path)
    {
        return base_path($path);
    }

    public function config($path)
    {
        return config_path($path);
    }

    public function database($path)
    {
        return database_path($path);
    }

    public function public($path)
    {
        return public_path($path);
    }

    public function statamic($path)
    {
        return statamic_path($path);
    }
}
