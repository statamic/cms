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

    /**
     * Fluently get or set property from func_get_args(), for when we want to allow the setting of `null`.
     *
     * @param string $property
     * @param array $funcArgs
     * @param null|\Closure $additionalSetLogic
     * @return $this
     */
    public function fluentlyGetOrSetFromArgs($property, $funcArgs, $additionalSetLogic = null)
    {
        if (count($funcArgs) === 0) {
            return $this->{$property};
        }

        $this->{$property} = $funcArgs[0];

        if ($additionalSetLogic) {
            $additionalSetLogic();
        }

        return $this;
    }
}
