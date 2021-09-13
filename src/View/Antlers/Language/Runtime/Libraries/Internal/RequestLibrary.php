<?php

namespace Statamic\View\Antlers\Language\Runtime\Libraries\Internal;

use Statamic\View\Antlers\Language\Runtime\Libraries\RuntimeLibrary;

class RequestLibrary extends RuntimeLibrary
{
    protected $name = 'request';

    public function __construct()
    {
        $this->exposedMethods = [
            'method' => 1,
            'root' => 1,
            'url' => 1,
            'fullUrl' => 1,
            'fullUrlWithQuery' => [
                $this->arrayVar('query'),
            ],
            'path' => 1,
            'decodedPath' => 1,
            'segment' => [
                $this->numericVar('index'),
                $this->stringVarWithDefault('default', null),
            ],
            'segments' => 1,
            'is' => [
                [
                    self::KEY_NAME => 'patterns',
                    self::KEY_ACCEPTS => [self::KEY_TYPE_STRING],
                    self::KEY_ACCEPTS_MANY => true,
                ],
            ],
            'routeIs' => [
                [
                    self::KEY_NAME => 'patterns',
                    self::KEY_ACCEPTS => [self::KEY_TYPE_STRING],
                    self::KEY_ACCEPTS_MANY => true,
                ],
            ],
            'fullUrlIs' => [
                [
                    self::KEY_NAME => 'patterns',
                    self::KEY_ACCEPTS => [self::KEY_TYPE_STRING],
                    self::KEY_ACCEPTS_MANY => true,
                ],
            ],
            'ajax' => 1,
            'pjax' => 1,
            'prefetch' => 1,
            'secure' => 1,
            'ip' => 1,
            'ips' => 1,
            'userAgent' => 1,
            'get' => [
                $this->stringVar('key'),
                $this->anyWithDefault('default', null),
            ],
            'json' => [
                $this->stringVarWithDefault('key', null),
                $this->anyWithDefault('default', null),
            ],
            'fingerprint' => 1,
            'toArray' => 1,
        ];
    }

    public function toArray()
    {
        return request()->toArray();
    }

    public function fingerprint()
    {
        return request()->fingerprint();
    }

    public function json($key = null, $default = null)
    {
        return request()->json($key, $default);
    }

    public function get($key, $default = null)
    {
        return request()->get($key, $default);
    }

    public function userAgent()
    {
        return request()->userAgent();
    }

    public function ips()
    {
        return request()->ips();
    }

    public function ip()
    {
        return request()->ip();
    }

    public function secure()
    {
        return request()->secure();
    }

    public function prefetch()
    {
        return request()->prefetch();
    }

    public function ajax()
    {
        return request()->ajax();
    }

    public function pjax()
    {
        return request()->pjax();
    }

    public function is($patterns)
    {
        return request()->is($patterns);
    }

    public function routeIs($patterns)
    {
        return request()->routeIs($patterns);
    }

    public function fullUrlIs($patterns)
    {
        return request()->fullUrlIs($patterns);
    }

    public function method()
    {
        return request()->method();
    }

    public function root()
    {
        return request()->root();
    }

    public function url()
    {
        return request()->url();
    }

    public function fullUrl()
    {
        return request()->fullUrl();
    }

    public function fullUrlWithQuery($query)
    {
        return request()->fullUrlWithQuery($query);
    }

    public function path()
    {
        return request()->path();
    }

    public function decodedPath()
    {
        return request()->decodedPath();
    }

    public function segment($index, $default = null)
    {
        return request()->segment($index, $default);
    }

    public function segments()
    {
        return request()->segments();
    }
}
