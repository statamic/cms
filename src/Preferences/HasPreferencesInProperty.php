<?php

namespace Statamic\Preferences;

use Statamic\Support\Arr;

trait HasPreferencesInProperty
{
    use HasPreferences;

    protected $preferences = [];

    protected function getPreferences()
    {
        return $this->preferences;
    }

    public function setPreferences($preferences)
    {
        $this->preferences = $preferences;

        return $this;
    }

    public function mergePreferences($preferences)
    {
        $this->preferences = array_merge($this->getPreferences(), Arr::wrap($preferences));

        return $this;
    }
}
