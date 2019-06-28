<?php

namespace Statamic\API\Endpoint;

class Compare
{

    /**
     * Compares two values
     *
     * Returns 1 if first is greater, -1 if second is, 0 if same
     *
     * @param mixed $one Value 1 to compare
     * @param mixed $two Value 2 to compare
     * @return int
     */
    public function values($one, $two)
    {
        // something is null
        if (is_null($one) || is_null($two)) {
            if (is_null($one) && !is_null($two)) {
                return 1;
            } elseif (!is_null($one) && is_null($two)) {
                return -1;
            }

            return 0;
        }

        // something is an array
        if (is_array($one) || is_array($two)) {
            if (is_array($one) && !is_array($two)) {
                return 1;
            } elseif (!is_array($one) && is_array($two)) {
                return -1;
            }

            return 0;
        }

        // something is an object
        if (is_object($one) || is_object($two)) {
            if (is_object($one) && !is_object($two)) {
                return 1;
            } elseif (!is_object($one) && is_object($two)) {
                return -1;
            }

            return 0;
        }

        // something is a boolean
        if (is_bool($one) || is_bool($two)) {
            if ($one && !$two) {
                return 1;
            } elseif (!$one && $two) {
                return -1;
            }

            return 0;
        }

        // string based
        if (!is_numeric($one) || !is_numeric($two)) {
            return Str::compare($one, $two, null, false);
        }

        // number-based
        if ($one > $two) {
            return 1;
        } elseif ($one < $two) {
            return -1;
        }

        return 0;
    }
}
