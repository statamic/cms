<?php

namespace Statamic\Preferences;

use Closure;
use Facades\Statamic\Preferences\CorePreferences;
use Illuminate\Support\Arr;
use Statamic\Facades\User;

class Preferences
{
    protected $dotted = [];
    protected $preventMergingChildren = [];
    protected $fields = [];
    protected $tabs = [];
    protected $pendingTab = null;
    protected $extensions = [];

    /**
     * Get default preferences instance.
     *
     * @return DefaultPreferences
     */
    public function default()
    {
        return app(DefaultPreferences::class);
    }

    /**
     * Prevent merging child data within a specific dotted preferences key.
     *
     * @param  string  $dottedKey
     */
    public function preventMergingChildren($dottedKey)
    {
        $this->preventMergingChildren[] = $dottedKey;
    }

    /**
     * Get all preferences, merged in a specific order for precedence.
     *
     * @return array
     */
    public function all()
    {
        if (auth()->guest()) {
            return [];
        }

        return $this
            ->resetState()
            ->mergeDottedUserPreferences()
            ->mergeDottedRolePreferences()
            ->mergeDottedDefaultPreferences()
            ->getMultiDimensionalPreferences();
    }

    /**
     * Get preference off user or role, respecting the precedence setup in `all()`.
     *
     * @param  mixed  $key
     * @param  mixed  $fallback
     * @return mixed
     */
    public function get($key, $fallback = null)
    {
        return Arr::get($this->all(), $key, $fallback);
    }

    /**
     * Reset state.
     *
     * @return $this
     */
    protected function resetState()
    {
        $this->dotted = [];

        return $this;
    }

    /**
     * Merged dotted user preferences.
     *
     * @return $this
     */
    protected function mergeDottedUserPreferences()
    {
        $this->dotted += $this->arrayDotPreferences(User::current()->preferences());

        return $this;
    }

    /**
     * Merged dotted role preferences.
     *
     * @return $this
     */
    protected function mergeDottedRolePreferences()
    {
        foreach (User::current()->roles() as $role) {
            $this->dotted += $this->arrayDotPreferences($role->preferences());
        }

        return $this;
    }

    /**
     * Merged dotted default preferences.
     *
     * @return $this
     */
    protected function mergeDottedDefaultPreferences()
    {
        $defaultPreferences = $this->default()->all();

        $this->dotted += $this->arrayDotPreferences($defaultPreferences);

        return $this;
    }

    /**
     * Array dot preferences array, while respecting `preventMergingChildren` property.
     *
     * @param  array  $array
     * @return array
     */
    protected function arrayDotPreferences($array)
    {
        $preserve = [];

        foreach ($this->preventMergingChildren as $dottedKey) {
            $childData = Arr::pull($array, $dottedKey);

            if (! is_null($childData)) {
                $preserve[$dottedKey] = $childData;
            }
        }

        return array_merge(Arr::dot($array), $preserve);
    }

    /**
     * Get multi-dimensional array of preferences from dotted preferences.
     *
     * @return array
     */
    protected function getMultiDimensionalPreferences()
    {
        $preferences = [];

        foreach ($this->dotted as $key => $value) {
            Arr::set($preferences, $key, $value);
        }

        return $preferences;
    }

    public function extend(Closure $callback)
    {
        $this->extensions[] = $callback;
    }

    public function boot()
    {
        $early = $this->fields;
        $this->fields = [];

        CorePreferences::boot();

        foreach ($this->extensions as $callback) {
            $return = $callback($this);

            if (is_array($return)) {
                foreach ($return as $handle => $tab) {
                    $display = $this->tabs[$handle] ?? $tab['display'] ?? $handle;
                    $this->tab($handle, $display, function () use ($tab) {
                        foreach ($tab['fields'] as $handle => $field) {
                            $this->register($handle, $field);
                        }
                    });
                }
            }
        }

        $this->fields = array_merge($this->fields, $early);
    }

    public function register($handle, $field = [])
    {
        $preference = self::make($handle, $field);

        $this->fields[] = $preference;

        return $preference;
    }

    public function make(string $handle, array $field = [])
    {
        $preference = (new Preference)->handle($handle)->field($field);

        if ($this->pendingTab) {
            $preference->tab($this->pendingTab);
        }

        return $preference;
    }

    public function tabs()
    {
        return collect($this->fields)
            ->groupBy->tab()
            ->map(fn ($fields, $tab) => [
                'display' => $this->tabs[$tab] ?? __('General'),
                'fields' => $fields->keyBy->handle()->map->field()->all(),
            ]);
    }

    public function tab($handle, $label, $permissions = null)
    {
        throw_if($this->pendingTab, new \Exception('Cannot nest preference tabs'));

        if (func_num_args() === 3) {
            $this->tabs[$handle] = $label;
        }

        if (func_num_args() === 2) {
            $permissions = $label;
        }

        $this->pendingTab = $handle;

        $permissions($this);

        $this->pendingTab = null;
    }
}
