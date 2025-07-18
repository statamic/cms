<?php

namespace Statamic\CP\Navigation;

use Illuminate\Support\Collection;
use Statamic\CommandPalette\Category;
use Statamic\CommandPalette\Link;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\URL;
use Statamic\Statamic;
use Statamic\Support\Html;
use Statamic\Support\Str;
use Statamic\Support\Svg;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class NavItem
{
    use FluentlyGetsAndSets;

    protected $display;
    protected $section;
    protected $id;
    protected $url;
    protected $icon;
    protected $children;
    protected $isChild;
    protected $wasOriginallyChild;
    protected $authorization;
    protected $active;
    protected $view;
    protected $order;
    protected $hidden;
    protected $manipulations;
    protected $original;
    protected $attributes;
    protected $extra;

    /**
     * Get or set display.
     *
     * @param  string|null  $display
     * @return mixed
     */
    public function display($display = null)
    {
        return $this->fluentlyGetOrSet('display')->value($display);
    }

    /**
     * Get or set section name.
     *
     * @param  string|null  $section
     * @return mixed
     */
    public function section($section = null)
    {
        return $this->fluentlyGetOrSet('section')->value($section);
    }

    /**
     * Get or set the ID for referencing in preferences.
     *
     * @param  string|null  $id
     * @return mixed
     */
    public function id($id = null)
    {
        return $this
            ->fluentlyGetOrSet('id')
            ->setter(function ($value) {
                return Str::endsWith($value, '::')
                    ? $value.static::snakeCase($this->display())
                    : $value;
            })
            ->getter(function ($value) {
                if ($value) {
                    return $value;
                }

                $section = static::snakeCase($this->section());
                $item = static::snakeCase($this->display());

                return "{$section}::{$item}";
            })
            ->value($id);
    }

    /**
     * Preserve current ID.
     *
     * @return $this
     */
    public function preserveCurrentId()
    {
        return $this->id($this->id());
    }

    /**
     * Set url by cp route name.
     *
     * @param  array|string  $name
     * @param  mixed  $params
     * @return mixed
     */
    public function route($name, $params = [])
    {
        return $this->url(cp_route($name, $params));
    }

    /**
     * Get or set URL.
     *
     * @param  string|null  $url
     * @return mixed
     */
    public function url($url = null)
    {
        return $this
            ->fluentlyGetOrSet('url')
            ->setter(function ($url) {
                if (Str::startsWith($url, ['http://', 'https://'])) {
                    return $url;
                }

                if (Str::startsWith($url, '/')) {
                    return url($url);
                }

                return url(config('statamic.cp.route').'/'.$url);
            })
            ->afterSetter(function ($url) {
                $cpUrl = url(config('statamic.cp.route')).'/';

                if (! $this->active && Str::startsWith($url, $cpUrl)) {
                    $this->active = $this->generateActivePatternForCpUrl($url);
                }
            })
            ->value($url);
    }

    /**
     * Generate active URL pattern to determine when to resolve children for `hasActiveChild()` checks.
     *
     * @param  string  $url
     * @return string
     */
    protected function generateActivePatternForCpUrl($url)
    {
        $cpUrl = url(config('statamic.cp.route')).'/';

        $relativeUrl = str_replace($cpUrl, '', URL::removeQueryAndFragment($url));

        return $relativeUrl.'(/(.*)?|$)';
    }

    /**
     * Generate active URL patterns for this item's children.
     *
     * @return Collection
     */
    protected function generateActivePatternsForChildren()
    {
        if (! $this->children()) {
            return collect();
        }

        return collect(NavBuilder::getUnresolvedChildrenUrlsForItem($this) ?? [])
            ->map(fn ($url) => $this->generateActivePatternForCpUrl($url));
    }

    /**
     * Get editable url for nav builder UI.
     */
    public function editableUrl()
    {
        if (! $this->url) {
            return null;
        }

        if (Str::startsWith($this->url, url('/'))) {
            return str_replace(url('/'), '', $this->url);
        }

        return $this->url;
    }

    /**
     * Get or set icon.
     *
     * @param  string|null  $icon
     * @return mixed
     */
    public function icon($icon = null)
    {
        return $this->fluentlyGetOrSet('icon')->args(func_get_args());
    }

    /**
     * Get icon as resolved renderable SVG.
     *
     * @return string
     */
    public function svg()
    {
        $value = $this->icon() ?? 'collections';

        if (! Str::startsWith($value, '<svg')) {
            $value = Statamic::svg("icons/{$value}");
        }

        return Svg::withClasses($value, 'size-4 shrink-0');
    }

    /**
     * Get or set HTML attributes.
     *
     * @param  array|null  $attrs
     * @return mixed
     */
    public function attributes($attrs = null)
    {
        return $this
            ->fluentlyGetOrSet('attributes')
            ->setter(function ($value) {
                return is_array($value) ? Html::attributes($value) : $value;
            })
            ->value($attrs);
    }

    /**
     * Get or set extra data.
     *
     * @param  array|null  $extra
     * @return mixed
     */
    public function extra($extra = null)
    {
        return $this
            ->fluentlyGetOrSet('extra')
            ->value($extra);
    }

    /**
     * Get or set child nav items.
     *
     * @param  array|null  $items
     * @param  bool  $generateNewIds
     * @return mixed
     */
    public function children($items = null, $generateNewIds = true)
    {
        if (is_null($items)) {
            return $this->children;
        }

        if (is_callable($items)) {
            $this->children = $items;

            return $this;
        }

        $this->children = collect($items)
            ->map(function ($value, $key) {
                return $value instanceof self
                    ? $value
                    : Nav::item($key)->url($value);
            })
            ->map(function ($navItem) use ($generateNewIds) {
                return $navItem
                    ->id($generateNewIds ? $this->id().'::' : $navItem->id())
                    ->icon($navItem->icon() ?? $this->icon())
                    ->section($this->section())
                    ->isChild(true);
            })
            ->values();

        if ($this->children->isEmpty()) {
            $this->children = null;
        }

        return $this;
    }

    /**
     * Track if this nav item is a child of another nav item.
     *
     * @param  bool|null  $isChild
     * @return mixed
     */
    public function isChild($isChild = null)
    {
        return $this
            ->fluentlyGetOrSet('isChild')
            ->getter(function ($value) {
                return (bool) $value;
            })
            ->afterSetter(function ($value) {
                if ($value === true && ! isset($this->wasOriginallyChild)) {
                    $this->wasOriginallyChild = $value;
                }
            })
            ->value($isChild);
    }

    /**
     * Check if current url is a restful descendant.
     */
    protected function currentUrlIsRestfulDescendant(): bool
    {
        return (bool) Str::endsWith(request()->url(), [
            '/create',
            '/edit',
        ]);
    }

    /**
     * Check if we should assume nested URL conventions for active state on children.
     */
    protected function doesntHaveExplicitChildren(): bool
    {
        return (bool) ! $this->children;
    }

    /**
     * Check if this nav item was ever a child before user preferences were applied.
     */
    protected function wasOriginallyChild(): bool
    {
        return (bool) $this->wasOriginallyChild;
    }

    /**
     * Resolve children closure.
     *
     * @return $this
     */
    public function resolveChildren()
    {
        if (! is_callable($this->children)) {
            return $this;
        }

        // Resolve children closure
        $this->children($this->children()());

        // Resolve children closure on synced original instance
        if ($this->original() && is_callable($this->original->children())) {
            $this->original()->children($this->original->children()());
        }

        // Sync original on each new child item
        if ($this->children()) {
            $this->children()->each(fn ($item) => $item->syncOriginal());
        }

        return $this;
    }

    /**
     * Get or set authorization.
     *
     * @param  string|null  $ability
     * @param  array  $arguments
     * @return mixed
     */
    public function authorization($ability = null, $arguments = [])
    {
        if (is_null($ability)) {
            return $this->authorization;
        }

        $this->authorization = (object) [
            'ability' => $ability,
            'arguments' => $arguments,
        ];

        return $this;
    }

    /**
     * Get or set authorization (an alias for consistency with Laravel's can() method).
     *
     * @param  string|null  $ability
     * @param  array  $arguments
     * @return mixed
     */
    public function can($ability = null, $arguments = [])
    {
        return $this->authorization($ability, $arguments);
    }

    /**
     * Determine whether the nav item is currently active.
     *
     * @return bool
     */
    public function isActive()
    {
        if ($this->hasActiveChild()) {
            return true;
        }

        // If the current URL is not explicitly referenced in the CP nav,
        // and if this item is/was ever a child nav item,
        // then check against URL heirarchy conventions using regex pattern.
        if ($this->currentUrlIsNotExplicitlyReferencedInNav()) {
            switch (true) {
                case $this->currentUrlIsRestfulDescendant():
                case $this->doesntHaveExplicitChildren():
                case $this->wasOriginallyChild():
                    return $this->isActiveByPattern($this->active);
            }
        }

        return request()->url() === URL::removeQueryAndFragment($this->url);
    }

    /**
     * Determine whether the nav item has a currently active child.
     *
     * @return bool
     */
    protected function hasActiveChild()
    {
        // If children are already resolved to a collection, just check `isActive()` on each child item.
        if ($this->children() instanceof Collection) {
            return $this
                ->children()
                ->filter(fn ($item) => $item->isActive())
                ->isNotEmpty();
        }

        // If the current URL is not explicitly referenced in the CP nav,
        // and if this item has children to check against,
        // then check against URL heirarchy conventions using regex pattern.
        if ($this->currentUrlIsNotExplicitlyReferencedInNav() && $this->children()) {
            return $this->isActiveByPattern($this->generateActivePatternsForChildren());
        }

        // If children closure has not been resolved, and children urls are cached, check against cached children.
        if ($childrenUrls = NavBuilder::getUnresolvedChildrenUrlsForItem($this)) {
            return collect($childrenUrls)
                ->map(fn ($url) => URL::removeQueryAndFragment($url))
                ->contains(request()->url());
        }

        return false;
    }

    /**
     * Determine whether the current URL is explicitly referenced in nav.
     *
     * @return bool
     */
    protected function currentUrlIsNotExplicitlyReferencedInNav()
    {
        return ! NavBuilder::getAllUrls()->contains(request()->url());
    }

    /**
     * Determine whether the nav item is currently active using the regex technique for deeply nested hierarchical urls.
     *
     * @param  string|array  $active
     * @return bool
     */
    protected function isActiveByPattern($active)
    {
        if (! $active) {
            return false;
        }

        return collect($active)
            ->map(fn ($pattern) => preg_quote(config('statamic.cp.route'), '#').'/'.$pattern)
            ->filter(fn ($pattern) => preg_match('#'.$pattern.'#', request()->decodedPath()) === 1)
            ->isNotEmpty();
    }

    /**
     * Get or set custom view.
     *
     * @param  string|null  $view
     * @return mixed
     */
    public function view($view = null)
    {
        return $this->fluentlyGetOrSet('view')->value($view);
    }

    /**
     * Get or set nav item order.
     *
     * @param  int|null  $order
     * @return mixed
     */
    public function order($order = null)
    {
        return $this->fluentlyGetOrSet('order')->value($order);
    }

    /**
     * Get or set hidden status.
     *
     * @param  bool|null  $hidden
     * @return mixed
     */
    public function hidden($hidden = null)
    {
        return $this->fluentlyGetOrSet('hidden')
            ->getter(function ($value) {
                return $value ?? false;
            })
            ->value($hidden);
    }

    /**
     * Get whether the nav item is to be hidden, but still made available for nav builder UI.
     *
     * @return bool
     */
    public function isHidden()
    {
        return $this->hidden();
    }

    /**
     * Get or set preferences manipulations.
     *
     * @param  string|null  $manipulations
     * @return mixed
     */
    public function manipulations($manipulations = null)
    {
        return $this->fluentlyGetOrSet('manipulations')->value($manipulations);
    }

    /**
     * Sync original state.
     *
     * @return $this
     */
    public function syncOriginal()
    {
        $this->original = null; // Clear original property so it can never appear in cloned instance.

        $this->original = clone $this;

        return $this;
    }

    /**
     * Get original state.
     *
     * @return mixed
     */
    public function original()
    {
        return $this->original;
    }

    /**
     * Alias for `display()`, left here for backwards compatibility.
     *
     * @param  string|null  $name
     * @return mixed
     */
    public function name(...$arguments)
    {
        return $this->display(...$arguments);
    }

    /**
     * Transform nav item and associated children to valid command palette `Link` instances.
     */
    public function commandPaletteLinks(?NavItem $parentItem = null): array
    {
        $displayItem = $parentItem ?? $this;

        $text = $displayItem->section() !== 'Top Level'
            ? __($displayItem->section()).' » '.__($displayItem->display())
            : __($displayItem->display());

        if ($parentItem) {
            $text .= ' » '.__($this->display());
        }

        $link = (new Link(text: $text, category: Category::Navigation))
            ->url($this->url())
            ->icon($this->icon());

        if ($children = $this->resolveChildren()->children()) {
            $childLinks = $children->flatMap(fn ($child) => $child->commandPaletteLinks($this));
        }

        return collect([$link])
            ->merge($childLinks ?? [])
            ->all();
    }

    /**
     * Convert to snake case.
     *
     * @param  string  $string
     * @return string
     */
    public static function snakeCase($string)
    {
        // Preserve colons `:` segments for child items and cloned ids.
        $string = Str::replace(':', '___colon___', $string);

        // Convert to lowercase and slug, removing all special characters.
        $string = Str::modifyMultiple($string, ['lower', 'slug']);

        // Convert to snake case.
        $string = Str::replace('-', '_', $string);

        // Put colons `:` back for child items and cloned ids.
        $string = Str::replace('___colon___', ':', $string);

        return $string;
    }
}
