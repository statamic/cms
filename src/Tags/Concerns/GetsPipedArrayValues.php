<?php

namespace Statamic\Tags\Concerns;

trait GetsPipedArrayValues
{
    /**
     * Explode piped array values from tag params into php array.
     *
     * @param  string  $string
     * @return array
     */
    protected function getPipedValues($string)
    {
        return collect(explode('|', $string))
            ->map(function ($value) {
                switch ($value) {
                    case 'true':
                        return true;
                    case 'false':
                        return false;
                    default:
                        return $value;
                }
            })
            ->all();
    }
}
