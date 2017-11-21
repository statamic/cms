<?php

namespace Statamic\Data;

use Statamic\Contracts\Data\Data as DataContract;
use Statamic\Contracts\Data\LocalizedData as LocalizedDataContract;

class LocalizedData implements LocalizedDataContract
{
    /**
     * @var string
     */
    protected $locale;

    /**
     * @var DataContract
     */
    protected $data;

    /**
     * @param string       $locale
     * @param DataContract $data
     */
    public function __construct($locale, DataContract $data)
    {
        $this->locale = $locale;
        $this->data = $data;
    }

    /**
     * Get the underlying data object
     *
     * @return \Statamic\Contracts\Data\Data
     */
    public function get()
    {
        // If no arguments are passed, get the object.
        if (empty(func_get_args())) {
            $data = clone $this->data;
            $data->locale($this->locale);
            return $data;
        }

        // Otherwise, they actually want to call ->get() on the object itself.
        return $this->call('get', func_get_args());
    }

    /**
     * Pass along any method calls to the underlying data object
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return $this->call($method, $args);
    }

    /**
     * Call a method on the underlying data object
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    private function call($method, $args = [])
    {
        $originalLocale = $this->data->locale();

        $this->data->locale($this->locale);

        $return = call_user_func_array([$this->data, $method], $args);

        $this->data->locale($originalLocale);

        // If the returned value if the data, we will re-wrap it in a
        // localized version to allow for localized chaining.
        if ($return === $this->data) {
            return new self($this->locale, $this->data);
        }

        return $return;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->call('toArray');
    }
}