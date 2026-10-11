<?php

namespace Statamic\View\Instrumentation\Antlers;

use Statamic\View\Instrumentation\HtmlSpec;

/** @internal */
class RegionExtents
{
    /** @return int|false|null Null when no Antlers region or PHP block starts at the offset. */
    public static function embeddedEndInBytes(string $source, int $start, int $length): int|false|null
    {
        $char = $source[$start] ?? '';

        if ($char === '{') {
            return ($source[$start + 1] ?? '') === '{' ? self::endInBytes($source, $start, $length) : null;
        }

        if ($char === '<' && self::startsPhpBlock($source, $start)) {
            return self::phpBlockEndInBytes($source, $start, $length);
        }

        return null;
    }

    public static function startsPhpBlock(string $source, int $start): bool
    {
        if (substr($source, $start, 3) === '<?=') {
            return true;
        }

        return strncasecmp(substr($source, $start, 5), '<?php', 5) === 0
            && HtmlSpec::isHtmlSpace($source[$start + 5] ?? "\n");
    }

    public static function phpBlockEndInBytes(string $source, int $start, int $length): int
    {
        for ($index = $start + 2; $index < $length; $index++) {
            $char = $source[$index];

            if ($char === '{' && ($source[$index + 1] ?? '') === '{' && $source[$index - 1] !== '@') {
                $end = self::endInBytes($source, $index, $length);

                if ($end !== false) {
                    $index = $end - 1;

                    continue;
                }
            }

            if ($char === '?' && ($source[$index + 1] ?? '') === '>') {
                return $index + 2;
            }
        }

        return $length;
    }

    public static function endInBytes(string $source, int $start, int $length): int|false
    {
        $terminator = match ($source[$start + 2] ?? '') {
            '#' => '#}}',
            '?' => '?}}',
            '$' => '$}}',
            default => null,
        };

        if ($terminator !== null) {
            $end = strpos($source, $terminator, $start + 3);

            return $end === false ? false : $end + 3;
        }

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
