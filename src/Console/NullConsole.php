<?php

namespace Statamic\Console;

class NullConsole
{
    protected $errors = [];
    protected $warnings = [];

    /**
     * Store error output.
     *
     * @param  string  $error
     * @return $this
     */
    public function error($error)
    {
        $this->errors[] = $error;

        return $this;
    }

    /**
     * Get error output.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getErrors()
    {
        return collect($this->errors);
    }

    /**
     * Store warning output.
     *
     * @param  string  $warning
     * @return $this
     */
    public function warn($warning)
    {
        $this->warnings[] = $warning;

        return $this;
    }

    /**
     * Get warning output.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getWarnings()
    {
        return collect($this->warnings);
    }

    /**
     * Any calls to this class just return itself so you can chain forever and ever.
     *
     * @param  string  $method
     * @param  array  $args
     * @return $this
     */
    public function __call($method, $args)
    {
        return $this;
    }
}
