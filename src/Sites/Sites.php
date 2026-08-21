<?php

namespace Statamic\Sites;

use Closure;
use Illuminate\Support\Collection;
use Statamic\Events\SiteCreated;
use Statamic\Events\SiteDeleted;
use Statamic\Events\SiteSaved;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Dictionary;
use Statamic\Facades\File;
use Statamic\Facades\User;
use Statamic\Facades\YAML;
use Statamic\Support\Str;

use function Statamic\trans as __;

class Sites
{
    const OTHER_GROUP_KEY = 'other';

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
        if (User::current()->isSuper()) {
            return $this->sites;
        }

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

    public function filterByGroup($handles, ?string $siteHandle)
    {
        if (! $siteHandle || ! ($site = $this->get($siteHandle))) {
            return collect($handles);
        }

        $groupKey = $site->groupHandle() ?? $site->group();

        if (! $groupKey) {
            return collect($handles);
        }

        return collect($handles)->filter(function ($handle) use ($groupKey) {
            $other = $this->get($handle);

            if (! $other) {
                return false;
            }

            return ($other->groupHandle() ?? $other->group()) === $groupKey;
        });
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

        $this->sites = $this->hydrateConfig($sites);

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
        $sites = File::exists($sitesPath = $this->path())
            ? YAML::file($sitesPath)->parse()
            : [];

        return $sites ?: $this->getFallbackConfig();
    }

    protected function getFallbackConfig()
    {
        return [
            'default' => [
                'name' => '{{ config:app:name }}',
                'url' => '/',
                'locale' => '{{ config:app:locale }}',
            ],
        ];
    }

    public function save()
    {
        // Track for `SiteCreated` and `SiteDeleted` events, before saving to file
        $newSites = $this->getNewSites();
        $deletedSites = $this->getDeletedSites();

        // Save sites to store
        $this->saveToStore();

        // Dispatch our tracked `SiteCreated` and `SiteDeleted` events
        $newSites->each(fn ($site) => SiteCreated::dispatch($site));
        $deletedSites->each(fn ($site) => SiteDeleted::dispatch($site));

        // Dispatch `SiteSaved` events
        $this->sites->each(fn ($site) => SiteSaved::dispatch($site));
    }

    protected function saveToStore()
    {
        File::put($this->path(), YAML::dump($this->config()));
    }

    public function blueprint(array $values = [])
    {
        $siteRowFields = $this->siteRowFields();

        if ($this->multiEnabled()) {
            return Blueprint::make()->setContents([
                'tabs' => [
                    'main' => [
                        'sections' => $this->multisiteBlueprintSections($siteRowFields, $values),
                    ],
                ],
            ]);
        }

        return Blueprint::make()->setContents([
            'fields' => $siteRowFields,
        ]);
    }

    public function blueprintValues(): array
    {
        if (! $this->multiEnabled()) {
            $site = $this->default();

            return array_merge(['handle' => $site->handle()], $site->rawConfig());
        }

        $values = [];

        foreach ($this->namedGroupSections() as $section) {
            $key = $section['key'];
            $mapped = $section['sites']->map(fn (Site $site) => $this->siteToBlueprintRow($site))->values()->all();

            $values["group_{$key}_name"] = $section['name'];
            $values["group_{$key}_sites"] = $mapped;
        }

        $values['group_'.self::OTHER_GROUP_KEY.'_sites'] = $this->otherSites()
            ->map(fn (Site $site) => $this->siteToBlueprintRow($site))
            ->values()
            ->all();

        return $values;
    }

    public function configFromBlueprintValues(array $values): array
    {
        if (! $this->multiEnabled()) {
            return [
                $values['handle'] => collect($values)->except(['id', 'handle'])->filter()->all(),
            ];
        }

        $sites = collect();

        foreach ($values as $key => $groupSites) {
            if (! is_array($groupSites) || ! preg_match('/^group_([A-Za-z0-9_-]+)_sites$/', $key, $matches)) {
                continue;
            }

            $groupKey = $matches[1];
            $groupName = $groupKey === self::OTHER_GROUP_KEY
                ? null
                : ($values["group_{$groupKey}_name"] ?? null);
            $groupName = is_string($groupName) && $groupName !== '' ? $groupName : null;

            $this->mergeSitesFromGrid($sites, $groupSites, $groupName, $groupKey);
        }

        return $sites
            ->keyBy('handle')
            ->map(fn ($site) => collect($site)->except(['id', 'handle'])->filter()->all())
            ->all();
    }

    protected function siteRowFields(): array
    {
        return [
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
                    'display' => __('URL'),
                    'instructions' => __('statamic::messages.site_configure_url_instructions'),
                    'required' => true,
                    'width' => 33,
                    'direction' => 'ltr',
                ],
            ],
            [
                'handle' => 'locale',
                'field' => [
                    'type' => 'select',
                    'display' => __('Locale'),
                    'instructions' => __('statamic::messages.site_configure_locale_instructions'),
                    'options' => [
                        '{{ config:app.locale }}' => '{{ config:app.locale }}',
                        ...Dictionary::find('locales')->options(),
                    ],
                    'taggable' => true,
                    'searchable' => true,
                    'max_items' => 1,
                    'required' => true,
                    'width' => 33,
                    'direction' => 'ltr',
                ],
            ],
            [
                'handle' => 'lang',
                'field' => [
                    'type' => 'dictionary',
                    'display' => __('Language'),
                    'instructions' => __('statamic::messages.site_configure_lang_instructions'),
                    'dictionary' => 'languages',
                    'max_items' => 1,
                    'width' => 33,
                    'direction' => 'ltr',
                    'clearable' => true,
                ],
            ],
            [
                'handle' => 'attributes',
                'field' => [
                    'display' => __('Custom Attributes'),
                    'instructions' => __('statamic::messages.site_configure_attributes_instructions'),
                    'type' => 'array',
                    'add_button' => __('Add Attribute'),
                    'compact' => true,
                ],
            ],
        ];
    }

    protected function multisiteBlueprintSections(array $siteRowFields, array $values = []): array
    {
        $sections = [];
        $seen = [];

        foreach ($this->namedGroupSections() as $section) {
            $seen[$section['key']] = true;
            $sections[] = $this->groupSection($section['key'], $section['name'], $siteRowFields);
        }

        foreach ($values as $handle => $groupSites) {
            if (! is_array($groupSites) || ! preg_match('/^group_(.+)_sites$/', $handle, $matches)) {
                continue;
            }

            $key = $matches[1];

            if ($key === self::OTHER_GROUP_KEY || isset($seen[$key])) {
                continue;
            }

            $name = $values["group_{$key}_name"] ?? null;
            $name = is_string($name) && $name !== '' ? $name : null;
            $seen[$key] = true;
            $sections[] = $this->groupSection($key, $name, $siteRowFields);
        }

        $sections[] = $this->groupSection(self::OTHER_GROUP_KEY, null, $siteRowFields);

        return $sections;
    }

    protected function groupSection(string $key, ?string $group, array $siteRowFields): array
    {
        $isOther = $key === self::OTHER_GROUP_KEY;

        $fields = [];

        if (! $isOther) {
            $fields[] = [
                'handle' => "group_{$key}_name",
                'field' => [
                    'type' => 'text',
                    'visibility' => 'hidden',
                    'always_save' => true,
                ],
            ];
        }

        $fields[] = [
            'handle' => "group_{$key}_sites",
            'field' => $this->sitesGridField($siteRowFields, $key),
        ];

        $section = [
            'display' => $isOther ? __('Other') : $group,
            'fields' => $fields,
        ];

        if (! $isOther) {
            $section['editable_title_handle'] = "group_{$key}_name";
            $section['reorderable'] = true;
        }

        return $section;
    }

    protected function sitesGridField(array $siteRowFields, string $groupKey): array
    {
        $tableWidths = [
            'attributes' => 30,
        ];

        return [
            'type' => 'grid',
            'hide_display' => true,
            'actions' => false,
            'fullscreen' => false,
            'mode' => 'table',
            'stack_at' => 925,
            'reorderable' => true,
            'headers_in_section' => true,
            'add_row' => __('Add Site'),
            'fields' => collect($siteRowFields)->map(function ($field) use ($tableWidths, $groupKey) {
                $field['field']['width'] = $tableWidths[$field['handle']] ?? 14;
                $field['field']['classes'] = 'max-w-48 min-w-0 overflow-hidden';

                if ($field['handle'] === 'handle' && $groupKey !== self::OTHER_GROUP_KEY) {
                    $field['field']['prefix_from'] = "group_{$groupKey}_name";
                }

                return $field;
            })->all(),
        ];
    }

    protected function siteToBlueprintRow(Site $site): array
    {
        return array_merge(
            ['handle' => $site->handle()],
            collect($site->rawConfig())->except('group', 'group_handle')->all()
        );
    }

    protected function otherSites(): Collection
    {
        return $this->all()->filter(fn (Site $site) => ! $site->group())->values();
    }

    protected function namedGroupSections(): Collection
    {
        $buckets = [];

        foreach ($this->all() as $site) {
            $name = $site->group();

            if (! $name) {
                continue;
            }

            $storedHandle = $site->groupHandle();
            $identity = ($storedHandle && $storedHandle !== self::OTHER_GROUP_KEY)
                ? "handle:{$storedHandle}"
                : "name:{$name}";

            if (! isset($buckets[$identity])) {
                $buckets[$identity] = [
                    'name' => $name,
                    'handle' => ($storedHandle && $storedHandle !== self::OTHER_GROUP_KEY) ? $storedHandle : null,
                    'sites' => collect(),
                ];
            }

            $buckets[$identity]['sites']->push($site);
        }

        $seen = [];
        $sections = collect();

        foreach ($buckets as $bucket) {
            $key = $this->uniqueGroupSectionKey($bucket['handle'], $bucket['name'], $seen);
            $seen[$key] = true;
            $sections->push([
                'key' => $key,
                'name' => $bucket['name'],
                'sites' => $bucket['sites'],
            ]);
        }

        return $sections;
    }

    protected function uniqueGroupSectionKey(?string $preferred, string $name, array $seen): string
    {
        if ($preferred && $preferred !== self::OTHER_GROUP_KEY && ! isset($seen[$preferred])) {
            return $preferred;
        }

        $base = Str::slug($name) ?: 'group';

        if ($base === self::OTHER_GROUP_KEY) {
            $base = 'group';
        }

        $key = $base;
        $suffix = 2;

        while (isset($seen[$key])) {
            $key = $base.'-'.$suffix++;
        }

        return $key;
    }

    protected function mergeSitesFromGrid(Collection $sites, array $groupSites, ?string $groupName, string $groupKey): void
    {
        foreach ($groupSites as $site) {
            $handle = $site['handle'] ?? null;

            if (! is_string($handle) || $handle === '') {
                continue;
            }

            if ($groupName) {
                $site['group'] = $groupName;
                $site['group_handle'] = $groupKey;
            } else {
                unset($site['group'], $site['group_handle']);
            }

            $sites->put($handle, $site);
        }
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

    protected function hydrateConfig($config): Collection
    {
        $defaultSiteHandle = collect($config)->keys()->first();

        return collect($config)->map(fn ($site, $handle) => new Site($handle, $site, $handle === $defaultSiteHandle));
    }

    protected function getNewSites(): Collection
    {
        $currentSites = $this->getSavedSites();
        $newSites = $this->config();

        return $this->hydrateConfig(
            collect($newSites)->diffKeys($currentSites)
        );
    }

    protected function getDeletedSites(): Collection
    {
        $currentSites = $this->getSavedSites();
        $newSites = $this->config();

        return $this->hydrateConfig(
            collect($currentSites)->diffKeys($newSites)
        );
    }
}
