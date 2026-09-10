<?php

namespace Statamic\View\Instrumentation\Antlers;

/** @internal */
class RegionExtents
{
    /**
     * @param  string  $source
     * @param  int  $start  Byte offset of the region's `{{`.
     * @param  int  $length
     * @return int|false
     */
    public static function endInBytes($source, $start, $length)
    {
        $index = $start + 2;

        while ($index < $length) {
            $char = $source[$index];
            $previous = $source[$index - 1];

            if ($char === '{' && $previous !== '@') {
                $depth = 0;

                while ($index < $length) {
                    $char = $source[$index];

                    if ($source[$index - 1] !== '@') {
                        if ($char === '{') {
                            $depth++;
                        } elseif ($char === '}') {
                            $depth--;

                            if ($depth === 0) {
                                break;
                            }
                        }
                    }

                    $index++;
                }

                $index++;

                continue;
            }

            if ($char === '}' && $previous !== '@' && ($source[$index + 1] ?? null) === '}') {
                return $index + 2;
            }

            $index++;
        }

        return false;
    }
}
