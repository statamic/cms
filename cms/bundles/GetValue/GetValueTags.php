<?php

namespace Statamic\Addons\GetValue;

use Statamic\API\Content;
use Statamic\Extend\Tags;

class GetValueTags extends Tags
{
    /**
     * Maps to {{ get_value:[field] }}
     *
     * @param  string $method
     * @param  array  $arguments
     * @return string
     */
    public function __call($method, $arguments)
    {
        $variable = $method;

        // Any parameters will be used for filtering the value unless we unset them.
        $filters = $this->parameters;

        // By default, the context that we'll use to look for the value will just be "the" context.
        $context = $this->context;

        // If a URL has been specified, we'll grab that and it as the context.
        if ($from = $this->get('from')) {
            $context = Content::whereUri($from)->toArray();
            unset($filters['from']);
        }

        // Get the value out of the context. If it doesn't exist, fall back to an empty array.
        $field_value = array_get($context, $variable, []);

        // The whole purpose of this tag is to get values from an array. If the field
        // is actually a string, we'll need to handle it a little differently.
        if (is_string($field_value)) {
            \Log::error('Value of requested variable is a string.');
            return $this->parseNoResults();
        }

        // Filter the collection down if there are filters.
        $field_value = $this->filter(collect($field_value), $filters);

        return ($field_value->isEmpty())
            ? $this->parseNoResults()
            : $this->parseLoop($field_value->all());
    }

    /**
     * Filter the array/collection
     *
     * @param \Illuminate\Support\Collection $collection
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    private function filter($collection, $filters)
    {
        if (empty($filters)) {
            return $collection;
        }

        return $collection->filter(function ($item) use ($filters) {
            $keep = true;

            foreach ($filters as $key => $value) {
                if (array_get($item, $key) != $value) {
                    $keep = false;
                }
            }

            return $keep;
        });
    }
}
