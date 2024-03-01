<?php

namespace Statamic\Sites;

use Closure;
use Statamic\Facades\File;
use Statamic\Facades\User;
use Statamic\Facades\YAML;
use Statamic\Support\Str;

class Sites
{
    protected $sites;
    protected $current;
    protected ?Closure $currentUrlCallback = null;

    public function __construct($sites = null)
    {
        $this->setSites($sites);
    }

    public function all()
    {
        return $this->sites;
    }

    public function authorized()
    {
        return $this->sites->filter(fn ($site) => User::current()->can('view', $site));
    }

    public function default()
    {
        return $this->sites->first();
    }

    public function hasMultiple()
    {
        return $this->sites->count() > 1;
    }

    public function get($handle)
    {
        return $this->sites->get($handle);
    }

    public function findByUrl($url)
    {
        $url = Str::before($url, '?');
        $url = Str::ensureRight($url, '/');

        return $this->sites
            ->filter(fn ($site) => Str::startsWith($url, Str::ensureRight($site->absoluteUrl(), '/')))
            ->sortByDesc
            ->url()
            ->first();
    }

    public function current()
    {
        return $this->current
            ?? $this->findByCurrentUrl()
            ?? $this->default();
    }

    private function findByCurrentUrl()
    {
        return $this->findByUrl(
            $this->currentUrlCallback ? call_user_func($this->currentUrlCallback) : request()->getUri()
        );
    }

    public function setCurrent($site)
    {
        $this->current = $this->get($site);
    }

    public function resolveCurrentUrlUsing(Closure $callback)
    {
        $this->currentUrlCallback = $callback;
    }

    public function selected()
    {
        return $this->get(session('statamic.cp.selected-site')) ?? $this->default();
    }

    public function setSelected($site)
    {
        session()->put('statamic.cp.selected-site', $site);
    }

    public function setSites($sites)
    {
        $sites ??= $this->getSavedSites();

        $this->sites = collect($sites)->map(fn ($site, $handle) => new Site($handle, $site));
    }

    public function setSiteValue($site, $key, $value)
    {
        if (! $this->sites->has($site)) {
            throw new \Exception("Could not find site [{$site}]");
        }

        $this->sites->get($site)?->set($key, $value);
    }

    protected function getSavedSites()
    {
        $default = [
            'default' => [
                'name' => config('app.name'),
                'locale' => 'en_US',
                'url' => '/',
            ],
        ];

        $sitesPath = base_path('content/sites.yaml');

        return File::exists($sitesPath)
            ? YAML::file($sitesPath)->parse()
            : $default;
    }

    /**
     * This is being replaced by `setSites()`.
     *
     * Though Statamic sites can be updated for this breaking change,
     * this gives time for addons to follow suit, and allows said
     * addons to continue working across versions for a while.
     *
     * @deprecated
     */
    public function setConfig($key, $value = null)
    {
        if (is_null($value)) {
            $this->setSites($key['sites']);

            return;
        }

        $keyParts = explode('.', $key);

        $this->setSiteValue($keyParts[1], $keyParts[2], $value);
    }
}
