<?php

namespace Statamic\Preferences;

use Illuminate\Support\Arr;
use Statamic\Facades\User;

class Preferences
{
    protected $user;
    protected $dotted = [];
    protected $preferences = [];

    /**
     * Instantiate preferences helpers.
     */
    public function __construct()
    {
        $this->user = User::current();
    }

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
     * Get all preferences, merged in a specific order for precedence.
     *
     * @return array
     */
    public function all()
    {
        if (auth()->guest()) {
            return [];
        }

        if ($this->preferences) {
            return $this->preferences;
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
        $this->preferences = [];

        return $this;
    }

    /**
     * Merged dotted user preferences.
     *
     * @return $this
     */
    protected function mergeDottedUserPreferences()
    {
        $this->dotted += Arr::dot($this->user->preferences());

        return $this;
    }

    /**
     * Merged dotted role preferences.
     *
     * @return $this
     */
    protected function mergeDottedRolePreferences()
    {
        foreach ($this->user->roles() as $role) {
            $this->dotted += Arr::dot($role->preferences());
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

        $this->dotted += Arr::dot($defaultPreferences);

        return $this;
    }

    /**
     * Get multi-dimensional array of preferences from dotted preferences.
     *
     * @return array
     */
    protected function getMultiDimensionalPreferences()
    {
        foreach ($this->dotted as $key => $value) {
            Arr::set($this->preferences, $key, $value);
        }

        return $this->preferences;
    }
}
