<?php

namespace Statamic\Preferences;

use Illuminate\Support\Arr;

class Preferences
{
    /**
     * Instantiate preferences helpers.
     */
    public function __construct()
    {
        $this->user = auth()->user();
    }

    /**
     * Get all preferences, merged in a specific order for precedence.
     *
     * @return array
     */
    public function all()
    {
        // TODO: Merging with roles
        return $this->user->preferences();
    }

    /**
     * Get preference off user or role, respecting the precedence setup in `all()`.
     *
     * @param mixed $key
     * @param mixed $fallback
     * @return mixed
     */
    public function get($key, $fallback = null)
    {
        return Arr::get($this->all(), $key, $fallback);
    }
}
