<?php

namespace Statamic\Extend\Contextual;

class ContextualBlink extends ContextualObject
{
    /**
     * @var  array
     */
    protected $data = [];

    /**
     * Gets blink data for a variable, or the $default if variable isn't set
     *
     * @param string  $key      Key to retrieve
     * @param mixed   $default  Default value to return
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->exists($key)) {
            return $this->data[$key];
        }

        return $default;
    }

    /**
     * Save blink data for a variable
     *
     * @param string  $key    Key to set
     * @param mixed   $value  Value to set
     */
    public function put($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Checks if a $key exists in the blink data
     *
     * @param string  $key  Key to set
     * @return boolean
     */
    public function exists($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Increment the $key by $count
     *
     * @param string $key
     * @param int    $increment
     */
    public function increment($key, $increment = 1)
    {
        $this->data[$key] = $this->data[$key] + $increment;
    }

    /**
     * Clears all blink data
     */
    public function clear()
    {
        $this->data = [];
    }

    /**
     * Get all blink data
     *
     * @return array
     */
    public function all()
    {
        return $this->data;
    }
}
