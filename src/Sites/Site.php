<?php

namespace Statamic\Sites;

use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\HasAugmentedData;
use Statamic\Support\Str;
use Statamic\Support\TextDirection;
use Statamic\View\Antlers\Language\Runtime\RuntimeParser;

class Site implements Augmentable
{
    use HasAugmentedData;

    protected $handle;
    protected $config;
    protected $rawConfig;

    public function __construct($handle, $config)
    {
        $this->handle = $handle;
        $this->config = $this->resolveAntlers($config);
        $this->rawConfig = $config;
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

    public function lang()
    {
        return $this->config['lang'] ?? $this->shortLocale();
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
        return TextDirection::of($this->lang());
    }

    public function attributes()
    {
        return $this->config['attributes'] ?? [];
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

    public function set($key, $value)
    {
        $this->config[$key] = $this->resolveAntlersValue($value);
        $this->rawConfig[$key] = $value;

        if ($key === 'url') {
            $this->absoluteUrlCache = null;
        }

        return $this;
    }

    public function resolveAntlers($config)
    {
        return collect($config)
            ->map(fn ($value) => $this->resolveAntlersValue($value))
            ->all();
    }

    protected function resolveAntlersValue($value)
    {
        if (is_array($value)) {
            return collect($value)
                ->map(fn ($element) => $this->resolveAntlersValue($element))
                ->all();
        }

        return (string) app(RuntimeParser::class)->parse($value, ['config' => config()->all()]);
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
            'lang' => $this->lang(),
            'locale' => $this->locale(),
            'short_locale' => $this->shortLocale(),
            'url' => $this->url(),
            'permalink' => $this->absoluteUrl(),
            'direction' => $this->direction(),
            'attributes' => $this->attributes(),
        ];
    }

    public function rawConfig()
    {
        return $this->rawConfig;
    }

    public function __toString()
    {
        return $this->handle();
    }
}
