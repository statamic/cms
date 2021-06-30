<?php

namespace Statamic\Sites;

use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\HasAugmentedData;
use Statamic\Support\Str;

class Site implements Augmentable
{
    use HasAugmentedData;

    protected $handle;
    protected $config;

    public function __construct($handle, $config)
    {
        $this->handle = $handle;
        $this->config = $config;
    }

    public function handle()
    {
        return $this->handle;
    }

    public function name()
    {
        return $this->config['name'];
    }

    public function locale()
    {
        return $this->config['locale'];
    }

    public function shortLocale()
    {
        return explode('-', str_replace('_', '-', $this->locale()))[0];
    }

    public function url()
    {
        $url = $this->config['url'];

        if ($url === '/') {
            return '/';
        }

        return Str::removeRight($url, '/');
    }

    public function direction()
    {
        return $this->config['direction'] ?? 'ltr';
    }

    public function absoluteUrl()
    {
        if (Str::startsWith($url = $this->url(), '/')) {
            $url = Str::ensureLeft($url, request()->getSchemeAndHttpHost());
        }

        return Str::removeRight($url, '/');
    }

    public function relativePath($url)
    {
        $url = Str::ensureRight($url, '/');

        $path = Str::removeLeft($url, $this->absoluteUrl());

        $path = Str::removeRight(Str::ensureLeft($path, '/'), '/');

        return $path === '' ? '/' : $path;
    }

    private function removePath($url)
    {
        $parsed = parse_url($url);

        return $parsed['scheme'].'://'.$parsed['host'];
    }

    public function augmentedArrayData()
    {
        return [
            'handle' => $this->handle(),
            'name' => $this->name(),
            'locale' => $this->locale(),
            'short_locale' => $this->shortLocale(),
            'url' => $this->url(),
            'direction' => $this->direction(),
        ];
    }

    public function __toString()
    {
        return $this->handle();
    }
}
