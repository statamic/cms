<?php

namespace Statamic\CP\Navigation;

use ArrayAccess;
use Statamic\Support\Str;

class UserNavConfig implements ArrayAccess
{
    protected $config;

    /**
     * Instantiate user nav config helper.
     *
     * @param  array  $userNavPreferences
     */
    public function __construct($userNavPreferences)
    {
        $this->config = $this->normalizeConfig($userNavPreferences);
    }

    /**
     * Normalize config.
     *
     * @param  array  $navConfig
     * @return array
     */
    protected function normalizeConfig($navConfig)
    {
        $navConfig = collect($navConfig);

        $normalized = collect()->put('reorder', $reorder = $navConfig->get('reorder', false));

        $sections = collect($navConfig->get('sections') ?? $navConfig->except('reorder'));

        $sections = $sections
            ->prepend($sections->pull('top_level') ?? '@inherit', 'top_level')
            ->map(fn ($config) => $this->normalizeSectionConfig($config))
            ->filter()
            ->reject(fn ($config) => $config === '@inherit' && ! $reorder)
            ->all();

        $normalized->put('sections', $sections);

        return $normalized->all();
    }

    /**
     * Normalize section config.
     *
     * @param  mixed  $sectionConfig
     * @return array
     */
    protected function normalizeSectionConfig($sectionConfig)
    {
        if (is_string($sectionConfig)) {
            return $sectionConfig;
        }

        $sectionConfig = collect($sectionConfig);

        $normalized = collect()->put('reorder', $reorder = $sectionConfig->get('reorder', false));

        $items = collect($sectionConfig->get('items') ?? $sectionConfig->except('reorder'));

        $items = $items
            ->map(function ($config) {
                return $this->normalizeItemConfig($config);
            })
            ->reject(function ($config) use ($reorder) {
                return isset($config['action']) && $config['action'] === '@inherit' && ! $reorder;
            })
            ->all();

        $normalized->put('items', $items);

        return $normalized->all();
    }

    /**
     * Normalize item config.
     *
     * @param  mixed  $itemConfig
     * @return array
     */
    protected function normalizeItemConfig($itemConfig)
    {
        $normalized = is_string($itemConfig)
            ? collect(['action' => Str::ensureLeft($itemConfig, '@')])
            : collect($itemConfig);

        if (! in_array($normalized->get('action'), ['@alias', '@move', '@inherit'])) {
            $normalized->put('action', $normalized->get('reorder') ? '@inherit' : '@alias');
        }

        return $normalized->all();
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return $this->config[$key];
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($key, $value)
    {
        throw new \Exception('Method offsetSet is not currently supported.');
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($key)
    {
        throw new \Exception('Method offsetExists is not currently supported.');
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($key)
    {
        throw new \Exception('Method offsetUnset is not currently supported.');
    }
}
