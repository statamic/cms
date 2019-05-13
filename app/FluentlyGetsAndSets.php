<?php

namespace Statamic;

trait FluentlyGetsAndSets
{
    /**
     * Fluently get or set property using the FluentGetterSetter helper class.
     *
     * @param string $property
     * @return FluentGetterSetter
     */
    public function fluentlyGetOrSet($property)
    {
        return new FluentGetterSetter($this, $property);
    }
}
