<?php

namespace Statamic\View\Antlers\Language\Utilities;

/**
 * Converts between UTF-8 byte offsets and character offsets without walking the
 * string in PHP. Byte offsets are what preg_* report; character offsets are what
 * the document parser and the rest of the Antlers runtime work with.
 *
 * Every conversion keeps the scan at the C level (preg / mbstring); a PHP per-byte
 * loop over a large template costs milliseconds per call, and the parser can make
 * hundreds of these calls while parsing a single document.
 */
class CharacterOffsets
{
    /**
     * PCRE limits a fixed repetition to 65535, so longer advances are split into
     * power-of-two steps. Restricting the steps to powers of two also keeps the
     * number of distinct compiled patterns tiny.
     */
    const MAX_ADVANCE_STEP = 32768;

    /**
     * @param  int[]  $offsets
     * @param  int  $length
     * @return array<int, int>
     */
    protected static function identity(array $offsets, $length)
    {
        $mapped = [];

        foreach ($offsets as $offset) {
            $mapped[$offset] = min($offset, $length);
        }

        return $mapped;
    }

    /**
     * @param  string  $source
     * @param  bool|null  $known  The caller's answer, when it already has one.
     * @return bool
     */
    protected static function isMultibyte($source, $known)
    {
        return $known ?? strlen($source) !== mb_strlen($source);
    }

    /**
     * Maps character offsets to byte offsets. Offsets past the end of the string
     * map to the byte length; negative offsets map to zero.
     *
     * @param  string  $source
     * @param  int[]  $characterOffsets
     * @param  bool|null  $sourceIsMultibyte  Pass it when known to skip the check.
     * @return array<int, int>
     */
    public static function toBytes($source, array $characterOffsets, $sourceIsMultibyte = null)
    {
        if ($characterOffsets === []) {
            return [];
        }

        $byteLength = strlen($source);

        if (! self::isMultibyte($source, $sourceIsMultibyte)) {
            return self::identity($characterOffsets, $byteLength);
        }

        // PCRE remembers a validated string, so this is free after the first call.
        if (! preg_match('//u', $source)) {
            return self::toBytesByteLoop($source, $characterOffsets);
        }

        $sorted = array_values(array_unique($characterOffsets));
        sort($sorted, SORT_NUMERIC);

        $offsets = [];
        $byte = 0;
        $character = 0;
        $reachedEnd = false;

        foreach ($sorted as $target) {
            if ($target <= 0) {
                $offsets[$target] = 0;

                continue;
            }

            if (! $reachedEnd && $target > $character) {
                $advanced = self::advanceCharacters($source, $byte, $target - $character);

                if ($advanced === null) {
                    $reachedEnd = true;
                } else {
                    $byte = $advanced;
                    $character = $target;
                }
            }

            $offsets[$target] = $reachedEnd ? $byteLength : $byte;
        }

        return $offsets;
    }

    /**
     * Returns the byte offset $characters code points after $byte, or null when the
     * string ends first. The source must be valid UTF-8.
     *
     * @param  string  $source
     * @param  int  $byte
     * @param  int  $characters
     * @return int|null
     */
    protected static function advanceCharacters($source, $byte, $characters)
    {
        $step = self::MAX_ADVANCE_STEP;

        while ($characters > 0) {
            if ($step > $characters) {
                $step >>= 1;

                continue;
            }

            if (preg_match('/\G.{'.$step.'}/us', $source, $match, 0, $byte) !== 1) {
                return null;
            }

            $byte += strlen($match[0]);
            $characters -= $step;
        }

        return $byte;
    }

    /**
     * The original per-byte conversion, kept for strings that are not valid UTF-8.
     *
     * @param  string  $source
     * @param  int[]  $characterOffsets
     * @return array<int, int>
     */
    protected static function toBytesByteLoop($source, array $characterOffsets)
    {
        $byteLength = strlen($source);
        $needed = array_fill_keys($characterOffsets, true);
        $offsets = [];
        $character = 0;

        for ($byte = 0; $byte < $byteLength; $byte++) {
            if ((ord($source[$byte]) & 0xC0) === 0x80) {
                continue;
            }

            if (isset($needed[$character])) {
                $offsets[$character] = $byte;
            }

            $character++;
        }

        foreach ($needed as $neededCharacter => $unused) {
            if (! isset($offsets[$neededCharacter])) {
                $offsets[$neededCharacter] = $neededCharacter <= 0 ? 0 : $byteLength;
            }
        }

        return $offsets;
    }

    /**
     * Maps byte offsets to character offsets. A byte inside a multibyte sequence maps
     * to the character that contains it; offsets past the end map to the length.
     *
     * When the caller already knows that byte $anchorByte is character
     * $anchorCharacter (a boundary), offsets at or after it are counted from there
     * instead of from the start of the string.
     *
     * @param  string  $source
     * @param  int[]  $byteOffsets
     * @param  bool|null  $sourceIsMultibyte  Pass it when known to skip the check.
     * @param  int  $anchorByte
     * @param  int  $anchorCharacter
     * @return array<int, int>
     */
    public static function toCharacters($source, array $byteOffsets, $sourceIsMultibyte = null, $anchorByte = 0, $anchorCharacter = 0)
    {
        if ($byteOffsets === []) {
            return [];
        }

        $byteLength = strlen($source);

        if (! self::isMultibyte($source, $sourceIsMultibyte)) {
            return self::identity($byteOffsets, $byteLength);
        }

        // Walk the requested offsets in order and count characters over each gap
        // with a C-level continuation-byte scan.
        $sorted = array_values(array_unique($byteOffsets));
        sort($sorted, SORT_NUMERIC);

        $offsets = [];
        $character = 0;
        $previousByte = 0;

        foreach ($sorted as $byte) {
            if ($byte >= $anchorByte && $previousByte < $anchorByte) {
                $previousByte = $anchorByte;
                $character = $anchorCharacter;
            }

            $lead = min($byte, $byteLength);

            while ($lead > 0 && $lead < $byteLength && (ord($source[$lead]) & 0xC0) === 0x80) {
                $lead--;
            }

            if ($lead > $previousByte) {
                $character += self::countCharacters($source, $previousByte, $lead);
                $previousByte = $lead;
            }

            $offsets[$byte] = $character;
        }

        return $offsets;
    }

    /**
     * Counts the non-continuation bytes in [$start, $end) with a C-level scan.
     *
     * @param  string  $source
     * @param  int  $start
     * @param  int  $end
     * @return int
     */
    protected static function countCharacters($source, $start, $end)
    {
        $length = $end - $start;

        return $length - preg_match_all('/[\x80-\xBF]/', substr($source, $start, $length));
    }
}
