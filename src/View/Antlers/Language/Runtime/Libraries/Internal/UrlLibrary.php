<?php

namespace Statamic\View\Antlers\Language\Runtime\Libraries\Internal;

use Statamic\View\Antlers\Language\Runtime\Libraries\RuntimeLibrary;

class UrlLibrary extends RuntimeLibrary
{
    protected $name = 'url';

    public function __construct()
    {
        $this->exposedMethods = [
            'full' => 1,
            'current' => 1,
            'previous' => [
                $this->stringVarWithDefault('fallback', false),
            ],
            'to' => [
                $this->stringVar('path'),
                $this->arrayVarWithDefault('extra', []),
                $this->boolVarWithDefault('secure', null),
            ],
            'secure' => [
                $this->stringVar('path'),
                $this->arrayVarWithDefault('parameters', []),
            ],
            'asset' => [
                $this->stringVar('path'),
                $this->boolVarWithDefault('secure', null),
            ],
            'secureAsset' => [
                $this->stringVar('path'),
            ],
            'assetFrom' => [
                $this->stringVar('root'),
                $this->stringVar('path'),
                $this->boolVarWithDefault('secure', null),
            ],
            'formatScheme' => [
                $this->boolVarWithDefault('secure', null),
            ],
            'route' => [
                $this->stringVar('name'),
                $this->arrayVarWithDefault('parameters', []),
                $this->boolVarWithDefault('absolute', true),
            ],
            'action' => [
                $this->strOrArrayVar('action'),
                $this->arrayVarWithDefault('parameters', []),
                $this->boolVarWithDefault('absolute', true),
            ],
            'formatParameters' => [
                $this->arrayVar('parameters'),
            ],
            'isValidUrl' => [
                $this->stringVar('path'),
            ],
        ];
    }

    public function current()
    {
        return url()->current();
    }

    public function full()
    {
        return url()->full();
    }

    public function previous($fallback = false)
    {
        return url()->previous($fallback);
    }

    public function to($path, $extra = [], $secure = null)
    {
        return url()->to($path, $extra, $secure);
    }

    public function secure($path, $parameters = [])
    {
        return url()->secure($path, $parameters);
    }

    public function asset($path, $secure = null)
    {
        return url()->asset($path, $secure);
    }

    public function secureAsset($path)
    {
        return url()->secureAsset($path);
    }

    public function assetFrom($root, $path, $secure = null)
    {
        return url()->assetFrom($root, $path, $secure);
    }

    public function formatScheme($secure = null)
    {
        return url()->formatScheme($secure);
    }

    public function route($name, $parameters = [], $absolute = true)
    {
        return url()->route($name, $parameters, $absolute);
    }

    public function action($action, $parameters = [], $absolute = true)
    {
        return url()->action($action, $parameters, $absolute);
    }

    public function formatParameters($parameters)
    {
        return url()->formatParameters($parameters);
    }

    public function isValidUrl($path)
    {
        return url()->isValidUrl($path);
    }
}
