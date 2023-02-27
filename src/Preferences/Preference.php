<?php

namespace Statamic\Preferences;

class Preference
{
    protected $handle;
    protected $field;
    protected $section = 'general';

    public function handle(string $handle = null)
    {
        if (func_num_args() === 0) {
            return $this->handle;
        }

        $this->handle = $handle;

        return $this;
    }

    public function field(array $field = null)
    {
        if (func_num_args() === 0) {
            return $this->field;
        }

        $this->field = $field;

        return $this;
    }

    public function section(string $section = null)
    {
        if (func_num_args() === 0) {
            return $this->section;
        }

        $this->section = $section;

        return $this;
    }
}
