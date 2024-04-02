<?php

namespace Statamic\Data;

use Statamic\Facades\Stache;

trait ReceivesIndexValues
{
    protected $indexedValues = [];

    /**
     * Get a list of indexes the class should receive values for.
     *
     * @return string[]
     */
    abstract public function receivesIndexValues();

    abstract public function getDependentIndexes();

    /**
     * Sets a value on the instance from a Stache index.
     *
     * @param  string  $index  The Stache index name.
     * @param  mixed  $value  The value.
     * @return $this
     */
    public function withIndexedValue(string $index, $value)
    {
        if (Stache::shouldUseIndexValues()) {
            $this->indexedValues[$index] = $value;
        }

        return $this;
    }

    /**
     * Get a value from the instance that was set from a Stache index.
     *
     * @param  string  $index  The Stache index name.
     * @return mixed
     */
    protected function getIndexedValue(string $index)
    {
        if (! Stache::shouldUseIndexValues()) {
            return null;
        }

        return $this->indexedValues[$index] ?? null;
    }

    /**
     * Remove an indexed value from the instance.
     *
     * @param  string  $index  The Stache index name.
     * @return $this
     */
    public function flushIndexedValue(string $index)
    {
        if (isset($this->indexedValues[$index])) {
            unset($this->indexedValues[$index]);
        }

        return $this;
    }

    /**
     * Remove all indexed values from the instance.
     *
     * @return $this
     */
    public function flushIndexedValues()
    {
        $this->indexedValues = [];

        return $this;
    }
}
