<?php

namespace Statamic\Console;

class NullConsole
{
    protected $errors = [];

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
