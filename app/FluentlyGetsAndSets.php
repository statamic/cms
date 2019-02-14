<?php

namespace Statamic;

trait FluentlyGetsAndSets
{
    /**
     * Fluently get or set property.
     *
     * @param string $property
     * @param mixed $value
     * @param null|\Closure $additionalSetLogic
     * @return $this
     */
    public function fluentlyGetOrSet($property, $value = null, $additionalSetLogic = null)
    {
        if (is_null($value)) {
            return $this->{$property};
        }

        $this->{$property} = $value;

        if ($additionalSetLogic) {
            $additionalSetLogic();
        }

        return $this;
    }
}
