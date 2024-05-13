<?php

namespace Statamic\Sites;

use Closure;
use Statamic\Facades\Blueprint;
use Statamic\Facades\File;
use Statamic\Facades\User;
use Statamic\Facades\YAML;
use Statamic\Support\Str;

class Sites
{
    protected $sites;
    protected $current;
    protected ?Closure $currentUrlCallback = null;

    public function __construct()
    {
        $this->setSites();
    }

    public function multiEnabled(): bool
    {
        return (bool) config('statamic.system.multisite', false);
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

    public function setSites($sites = null): self
    {
        $sites ??= $this->getSavedSites();

        $this->sites = collect($sites)->map(fn ($site, $handle) => new Site($handle, $site));

        return $this;
    }

    public function setSiteValue(string $site, string $key, $value): self
    {
        if (! $this->sites->has($site)) {
            throw new \Exception("Could not find site [{$site}]");
        }

        $this->sites->get($site)?->set($key, $value);

        return $this;
    }

    public function path(): string
    {
        return resource_path('sites.yaml');
    }

    protected function getSavedSites()
    {
        $default = [
            'default' => [
                'name' => '{{ config:app:name }}',
                'url' => '/',
                'locale' => 'en_US',
            ],
        ];

        return File::exists($sitesPath = $this->path())
            ? YAML::file($sitesPath)->parse()
            : $default;
    }

    public function save()
    {
        File::put($this->path(), YAML::dump($this->config()));
    }

    public function blueprint()
    {
        $siteFields = [
            [
                'handle' => 'name',
                'field' => [
                    'type' => 'text',
                    'instructions' => __('statamic::messages.site_configure_name_instructions'),
                    'required' => true,
                    'width' => 50,
                ],
            ],
            [
                'handle' => 'handle',
                'field' => [
                    'type' => 'slug',
                    'separator' => '_',
                    'generate' => true,
                    'instructions' => __('statamic::messages.site_configure_handle_instructions'),
                    'show_regenerate' => true,
                    'from' => 'name',
                    'required' => true,
                    'width' => 50,
                ],
            ],
            [
                'handle' => 'url',
                'field' => [
                    'type' => 'text',
                    'instructions' => __('statamic::messages.site_configure_url_instructions'),
                    'required' => true,
                    'width' => 33,
                    'direction' => 'ltr',
                ],
            ],
            [
                'handle' => 'locale',
                'field' => [
                    'type' => 'text',
                    'instructions' => __('statamic::messages.site_configure_locale_instructions'),
                    'required' => true,
                    'width' => 33,
                    'direction' => 'ltr',
                ],
            ],
            [
                'handle' => 'lang',
                'field' => [
                    'type' => 'text',
                    'instructions' => __('statamic::messages.site_configure_lang_instructions'),
                    'width' => 33,
                    'direction' => 'ltr',
                ],
            ],
            [
                'handle' => 'attributes',
                'field' => [
                    'display' => __('Custom Attributes'),
                    'instructions' => __('statamic::messages.site_configure_attributes_instructions'),
                    'type' => 'array',
                    'add_button' => __('Add Attribute'),
                ],
            ],
        ];

        // If multisite, nest fields in a grid
        if ($this->multiEnabled()) {
            $siteFields = [
                [
                    'handle' => 'sites',
                    'field' => [
                        'type' => 'grid',
                        'hide_display' => true,
                        'fullscreen' => false,
                        'mode' => 'stacked',
                        'add_row' => __('Add Site'),
                        'fields' => $siteFields,
                        'required' => true,
                    ],
                ],
            ];
        }

        return Blueprint::make()->setContents([
            'fields' => $siteFields,
        ]);
    }

    public function config(): array
    {
        return $this->sites
            ->keyBy
            ->handle()
            ->map
            ->rawConfig()
            ->all();
    }

    /**
     * Deprecated! This is being replaced by `setSites()` and `setSiteValue()`.
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
